Overall Workflow

#Frontend:

- Users upload files (drag-and-drop with progress and delete options).
  
- Validation: Max file size of 99 MB, up to 10 files.

- Upload progress and feedback (e.g., success or error messages).

#Backend:

- Store uploaded files temporarily.

- Use a C++ script to extract data from files using OCR and process it into a JSON format.

- Sanitize the JSON data (abbreviations, phonetic shortening, whitespace trimming).

- Submit the sanitized JSON to OpenAI API for analysis.

- Generate a PDF report from OpenAI’s response using FPDF.

- Provide the PDF for download and delete all temporary files after download.
