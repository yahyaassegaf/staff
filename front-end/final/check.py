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
            
            # Simple check
            if 'async function remove' in content and 'confirm(' in content:
                print(f"Found in {filepath}")
                count += 1
                
print(f"Total files: {count}")
