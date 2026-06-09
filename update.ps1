$files = Get-ChildItem -Path "c:\laragon\www\staff\front-end\final\src\components" -Filter "index.vue" -Recurse

foreach ($file in $files) {
    $content = [System.IO.File]::ReadAllText($file.FullName)
    if ($content.Contains('pakai_tanda_tangan')) {
        $content = $content.Replace("pakai_tanda_tangan: false,", "petanda_tangan: 'tidak',")
        $content = [regex]::Replace($content, "form\.pakai_tanda_tangan\s*=\s*val\.pakai_tanda_tangan\s*\?\?\s*false;", "form.petanda_tangan = val.petanda_tangan ?? 'tidak';")
        $content = $content.Replace(":name=`"'pakai_tanda_tangan_`"", ":name=`"'petanda_tangan_`"")
        $content = [regex]::Replace($content, ":value=`"true`"\s+v-model=`"form\.pakai_tanda_tangan`"", "value=`"ya`" v-model=`"form.petanda_tangan`"")
        $content = [regex]::Replace($content, ":value=`"false`"\s+v-model=`"form\.pakai_tanda_tangan`"", "value=`"tidak`" v-model=`"form.petanda_tangan`"")
        $content = $content.Replace("errors?.pakai_tanda_tangan", "errors?.petanda_tangan")
        $content = $content.Replace("errors.pakai_tanda_tangan[0]", "errors.petanda_tangan[0]")
        $content = [regex]::Replace($content, "form\.pakai_tanda_tangan\s*=\s*false;", "form.petanda_tangan = 'tidak';")

        [System.IO.File]::WriteAllText($file.FullName, $content)
        Write-Host "Updated: $($file.FullName)"
    }
}
Write-Host "Done!"
