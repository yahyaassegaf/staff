import os
import re

base_dir = r"c:\laragon\www\staff\front-end\final\src\view"
count = 0
for root, dirs, files in os.walk(base_dir):
    for file in files:
        if file == "index.vue":
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            if 'import Swal from "sweetalert2";' in content and 'Swal.fire(' not in content:
                # Remove the import since it's unused
                content = content.replace('import Swal from "sweetalert2";\n', '')
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(content)
                count += 1
                
print(f"Removed unused Swal import from {count} files.")
