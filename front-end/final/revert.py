import os
import re

def revert_swal(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    if 'Swal.fire({' not in content:
        return
        
    # Remove import Swal
    content = re.sub(r'import Swal from "sweetalert2";\n?', '', content)
    
    # Match the Swal.fire block
    pattern = r'Swal\.fire\(\{[\s\S]*?cancelButtonText:\s*"Batal",\s*\}\)\.then\(async\s*\(result\)\s*=>\s*\{\s*if\s*\(result\.isConfirmed\)\s*\{'
    
    replacement = 'if (!confirm("Apakah Anda yakin ingin menghapus data ini?")) return;'
    
    content = re.sub(pattern, replacement, content)
    
    # Now we need to remove the closing tags } });} which were added at the end of the try-catch block.
    # It usually looks like:
    #         }
    #       });}
    
    closing_pattern = r'\s*\}\s*\n\s*\}\);\s*\}'
    
    # We will just find the last occurence of });} after the try-catch of remove() or replace the specific closing that matches.
    # To be safer, since we only have one Swal in remove(), let's replace \n        }\n      });} with just \n    }
    
    # The original was:
    #     }
    # 
    #     async function download(params: any) {
    # 
    # And it became:
    #     
    #         }
    #       });}
    # 
    #     async function download(params: any) {
    
    # Let's try replacing the specific Swal closing block
    content = re.sub(r'\n\s*\}\n\s*\}\);\s*\}', '\n    }', content)
    
    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

base_dir = r"c:\laragon\www\staff\front-end\final\src\view"
for root, dirs, files in os.walk(base_dir):
    for file in files:
        if file == "index.vue":
            revert_swal(os.path.join(root, file))

print("Reverted Swal in all views")
