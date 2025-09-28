<?php
include 'header.php';
include 'db.php';

echo "<h2>Galeria de Fotos</h2>";

$stmt = $pdo->query("SELECT foto_path, titulo FROM sobre WHERE foto_path IS NOT NULL AND foto_path != ''");
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($fotos) {
    foreach ($fotos as $foto) {
        echo "<div style='display:inline-block;text-align:center;margin:10px;'>
            <img src='uploads/" . htmlspecialchars($foto['foto_path']) . "' alt='foto' style='width:120px;height:auto;'><br>
            <span>" . htmlspecialchars($foto['titulo']) . "</span>
        </div>";
    }
} else {
    echo "<p>Nenhuma foto cadastrada.</p>";
}

include 'footer.php';
?>