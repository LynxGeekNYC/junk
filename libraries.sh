#!/bin/bash

# Directory to store dependencies
DEPENDENCIES_DIR="./scripts"

# Create the directory if it doesn't exist
mkdir -p "$DEPENDENCIES_DIR"

# Function to download and install dependencies into the specified directory
install_dependencies() {
    echo "Installing dependencies to $DEPENDENCIES_DIR..."

    cd "$DEPENDENCIES_DIR" || exit

    # JsonCpp
    if [ ! -d "jsoncpp" ]; then
        echo "Cloning JsonCpp library..."
        git clone https://github.com/open-source-parsers/jsoncpp.git
        cd jsoncpp || exit
        mkdir -p build && cd build || exit
        cmake ..
        make -j$(nproc)
        cd ../..
    fi

    # Poppler
    if [ ! -d "poppler" ]; then
        echo "Cloning Poppler library..."
        git clone https://anongit.freedesktop.org/git/poppler/poppler.git
        cd poppler || exit
        mkdir -p build && cd build || exit
        cmake ..
        make -j$(nproc)
        cd ../..
    fi

    # Cairo
    if [ ! -d "cairo" ]; then
        echo "Cloning Cairo library..."
        git clone https://gitlab.freedesktop.org/cairo/cairo.git
        cd cairo || exit
        meson setup build
        ninja -C build
        cd ..
    fi

    # GDK Pixbuf
    if [ ! -d "gdk-pixbuf" ]; then
        echo "Cloning GDK Pixbuf library..."
        git clone https://gitlab.gnome.org/GNOME/gdk-pixbuf.git
        cd gdk-pixbuf || exit
        meson setup build
        ninja -C build
        cd ..
    fi

    # Tesseract
    if [ ! -d "tesseract" ]; then
        echo "Cloning Tesseract OCR library..."
        git clone https://github.com/tesseract-ocr/tesseract.git
        cd tesseract || exit
        mkdir -p build && cd build || exit
        cmake ..
        make -j$(nproc)
        cd ../..
    fi

    # Leptonica
    if [ ! -d "leptonica" ]; then
        echo "Cloning Leptonica library..."
        git clone https://github.com/DanBloomberg/leptonica.git
        cd leptonica || exit
        mkdir -p build && cd build || exit
        cmake ..
        make -j$(nproc)
        cd ../..
    fi

    echo "All dependencies have been installed to $DEPENDENCIES_DIR."
}

# Execute the function
install_dependencies

# Inform the user
echo "Setup complete. Dependencies are available in $DEPENDENCIES_DIR."
