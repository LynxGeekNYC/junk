#include <iostream>
#include <fstream>
#include <memory>
#include <jsoncpp/json/json.h> // JSONCPP header
#include <poppler/cpp/poppler-document.h>
#include <poppler/cpp/poppler-page-renderer.h>
#include <cairo/cairo.h>
#include <cairo/cairo-pdf.h>
#include <poppler/cpp/poppler-page.h>
#include <gdk-pixbuf/gdk-pixbuf.h>
#include <tesseract/baseapi.h>
#include <leptonica/allheaders.h>

int main(int argc, char *argv[]) {
    if (argc < 2) {
        std::cerr << "Usage: " << argv[0] << " <pdf_file>" << std::endl;
        return 1;
    }

    std::string pdf_path = argv[1];

    // Load the PDF document
    auto deleter = [](poppler::document *ptr) { delete ptr; };
    std::unique_ptr<poppler::document, decltype(deleter)> doc(poppler::document::load_from_file(pdf_path), deleter);

    if (!doc) {
        std::cerr << "Failed to load PDF file: " << pdf_path << std::endl;
        return 1;
    }

    Json::Value root;
    root["file_name"] = pdf_path;

    // Initialize Tesseract OCR
    tesseract::TessBaseAPI ocr;
    if (ocr.Init(NULL, "eng")) {
        std::cerr << "Could not initialize Tesseract OCR." << std::endl;
        return 1;
    }

    // Process each page
    for (int i = 0; i < doc->pages(); ++i) {
        std::cout << "Processing page " << i + 1 << " of " << doc->pages() << std::endl;

        poppler::page *p = doc->create_page(i);
        if (!p) {
            std::cerr << "Failed to load page " << i + 1 << std::endl;
            continue;
        }

        std::string page_text = p->text().to_latin1();
        if (page_text.empty()) {
            std::cout << "No text found, using OCR for page " << i + 1 << std::endl;

            // Render page to GdkPixbuf for OCR
            GdkPixbuf *pixbuf = p->render_to_pixbuf(0, 0, p->page_rect().width(), p->page_rect().height(), 300.0 / 72.0, 300.0 / 72.0);
            if (pixbuf) {
                int width = gdk_pixbuf_get_width(pixbuf);
                int height = gdk_pixbuf_get_height(pixbuf);
                int rowstride = gdk_pixbuf_get_rowstride(pixbuf);
                guchar *pixels = gdk_pixbuf_get_pixels(pixbuf);

                // Convert GdkPixbuf to Pix for Tesseract
                Pix *pix = pixCreate(width, height, 32);
                for (int y = 0; y < height; ++y) {
                    for (int x = 0; x < width; ++x) {
                        guchar *pixel = pixels + y * rowstride + x * 3;
                        uint32_t color = (pixel[0] << 16) | (pixel[1] << 8) | pixel[2];
                        pixSetPixel(pix, x, y, color);
                    }
                }

                ocr.SetImage(pix);
                page_text = ocr.GetUTF8Text();
                pixDestroy(&pix);

                g_object_unref(pixbuf);
            } else {
                std::cerr << "Failed to render page " << i + 1 << " for OCR." << std::endl;
            }
        }

        root["pages"][i] = page_text;
        delete p;
    }

    ocr.End();

    // Write JSON output
    Json::StreamWriterBuilder writer;
    writer["indentation"] = "  "; // Pretty print
    std::string json_str = Json::writeString(writer, root);

    std::ofstream output("output.json");
    output << json_str;
    output.close();

    std::cout << "JSON output saved to output.json" << std::endl;

    return 0;
}
