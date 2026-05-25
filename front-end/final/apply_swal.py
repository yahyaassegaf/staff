import os
import re

def replace_confirm(content):
    # Add import Swal if not exists
    if 'import Swal from "sweetalert2";' not in content:
        content = re.sub(r'(<script[^>]*>\n)', r'\1import Swal from "sweetalert2";\n', content, count=1)
        
    # find sync function remove
    start_idx = content.find('async function remove(')
    if start_idx == -1: return content
    
    # find the open brace of remove
    brace_idx = content.find('{', start_idx)
    
    # find the end of the remove function by matching braces
    count = 1
    end_idx = -1
    for i in range(brace_idx + 1, len(content)):
        if content[i] == '{': count += 1
        elif content[i] == '}': count -= 1
        
        if count == 0:
            end_idx = i
            break
            
    if end_idx == -1: return content
    
    remove_body = content[brace_idx+1:end_idx]
    
    swal_code = '''
      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, hapus!",
        cancelButtonText: "Batal",
      }).then(async (result) => {
        if (result.isConfirmed) {'''
    
    # Use re.DOTALL so [^)]+ doesn't fail on newlines? Actually [^)]+ inherently matches newlines.
    new_body = re.sub(r'if\s*\(!confirm\([^)]+\)\)\s*return;', swal_code, remove_body)
    
    if new_body != remove_body:
        # We need to inject closing brackets before the last whitespace in new_body, 
        # or just append to new_body.
        # But wait! If the body ended with   }, appending it blindly works, 
        # but to keep it pretty we can just append it:
        new_body = new_body.rstrip() + "\n        }\n      });\n    "
        content = content[:brace_idx+1] + new_body + content[end_idx:]
        return content
    else:
        return content

base_dir = r"c:\laragon\www\staff\front-end\final\src\view"
success_count = 0
for root, dirs, files in os.walk(base_dir):
    for file in files:
        if file == "index.vue":
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            new_content = replace_confirm(content)
            
            if new_content != content:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                success_count += 1
                
print(f"Successfully processed {success_count} files.")
