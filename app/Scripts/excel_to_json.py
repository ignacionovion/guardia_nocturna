import sys
import json
import openpyxl

def read_excel(file_path):
    try:
        workbook = openpyxl.load_workbook(file_path, data_only=True)
        sheet = workbook.active
        
        data = []
        for row in sheet.iter_rows(values_only=True):
            # Convertir valores a string para evitar problemas de serialización
            row_data = [str(cell) if cell is not None else "" for cell in row]
            data.append(row_data)
            
        return json.dumps(data)
    except Exception as e:
        return json.dumps({"error": str(e)})

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No file path provided"}))
        sys.exit(1)
        
    file_path = sys.argv[1]
    print(read_excel(file_path))
