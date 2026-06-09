import os
import re

pdf_dir = r"c:\laragon\www\staff\back-end\staff.app\resources\views\pdf"

count = 0
for filename in os.listdir(pdf_dir):
    if not filename.endswith(".blade.php"):
        continue
        
    filepath = os.path.join(pdf_dir, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
        
    original = content
    
    # Replace background position from 20% 50% or 0% 50% to 10px center
    content = re.sub(r'background-position:\s*(?:20%|0%)\s*50%;', 'background-position: 10px center;', content)
    
    # Replace signature img style width and margin
    content = re.sub(r'<img\s+src="\{\{\s*\$ttd\s*\}\}"\s+style="width:\s*\d+px;?">', '<img src="{{ $ttd }}" style="width:200px; margin-left:70px;">', content)
    
    if content != original:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(content)
        count += 1
        print(f"Updated {filename}")

print(f"Total updated: {count}")
