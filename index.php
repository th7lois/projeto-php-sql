<?php
include 'header.php';
include 'db.php';

// Buscar o último registro cadastrado
$stmt = $pdo->query("SELECT * FROM sobre ORDER BY created_at DESC LIMIT 1");
$sobre = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h2>Bem-vindo!</h2>";

if ($sobre) {
    echo "<h3>" . htmlspecialchars($sobre['titulo']) . "</h3>";
    if ($sobre['foto_path']) {
        echo "<img src='uploads/" . htmlspecialchars($sobre['foto_path']) . "' alt='Foto' style='max-width:300px;'><br>";
    }
    echo "<p>" . nl2br(htmlspecialchars($sobre['descricao'])) . "</p>";
} else {
    echo "<p>Nenhuma informação cadastrada ainda.</p>";
}

include 'footer.php';
?>