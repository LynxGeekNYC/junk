#include <iostream>
#include <fstream>
#include <string>
#include <nlohmann/json.hpp> // Use the nlohmann JSON library
#include <tesseract/baseapi.h>
#include <leptonica/allheaders.h>

int main(int argc, char *argv[]) {
    if (argc < 3) {
        std::cerr << "Usage: process_files <input_directory> <output_file>\n";
        return 1;
    }

    std::string inputDir = argv[1];
    std::string outputFile = argv[2];

    nlohmann::json jsonData;

    // Perform OCR on files in the directory
    for (const auto &entry : std::filesystem::directory_iterator(inputDir)) {
        std::string filePath = entry.path().string();
        Pix *image = pixRead(filePath.c_str());

        tesseract::TessBaseAPI api;
        api.Init(nullptr, "eng", tesseract::OEM_LSTM_ONLY);
        api.SetImage(image);

        std::string extractedText = api.GetUTF8Text();
        jsonData[filePath] = extractedText;

        api.End();
        pixDestroy(&image);
    }

    // Write JSON output
    std::ofstream outFile(outputFile);
    outFile << jsonData.dump(4);
    outFile.close();

    return 0;
}
