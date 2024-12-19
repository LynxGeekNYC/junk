Overall Workflow: User uploads file for OpenAI to analyze and generate a report as a downloadable PDF (FPDF).

Job Requirements: I have multiple variations of the script and I need to bring all the good functions together, etc and make it work. 

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

#OCR

- Both C++ need to merge
- All header libraries need to be included in the directory of the c++ for easier compiling. 
