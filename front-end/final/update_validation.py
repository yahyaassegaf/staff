import os
import glob

components_dir = r'c:\laragon\www\staff\front-end\final\src\components'
vue_files = glob.glob(os.path.join(components_dir, '**', '*.vue'), recursive=True)

modified_files = []

for file in vue_files:
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()

    original_content = content

    content = content.replace("errors?.no_surat }", "errors?.nomor_surat || errors?.no_surat }")
    content = content.replace("errors?.no_surat\"", "errors?.nomor_surat || errors?.no_surat\"")
    content = content.replace("{{ errors.no_surat[0] }}", "{{ errors.nomor_surat ? errors.nomor_surat[0] : errors.no_surat[0] }}")

    if content != original_content:
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        modified_files.append(file)

print('Modified files:')
for mf in modified_files:
    print(mf)
