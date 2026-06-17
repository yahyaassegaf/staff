<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=staffapp", "root", "");
    // Update posisi_template table
    $stmt = $pdo->prepare("UPDATE posisi_template SET alignment = 'left' WHERE field_name = 'tempat_tanggal_lahir'");
    $stmt->execute();
    
    // Also we need to shift the X coordinate so it doesn't overlap.
    // If it was centered at X=300, and we make it left, it will start at 300 and go right.
    // Wait, the user already adjusted the X coordinate in the editor to make it look right.
    // If they already adjusted it while it was centered, and we just change it to left, it will start exactly at that X.
    // Wait, if it was centered at X=380, the left edge was 380 - (width/2). 
    // If we just change to left, the left edge will be 380. So it will shift right!
    // Let's not guess the X shift. Just change the alignment to left.
    // Wait, if I just change the alignment to left, the user can adjust the X coordinate easily in the editor.
    // Let's just output success.
    
    echo "Successfully updated alignment to left for tempat_tanggal_lahir.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
