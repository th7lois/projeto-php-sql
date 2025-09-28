<?php
include 'header.php';
include 'db.php';

// Função para upload de imagem
function uploadFoto($file) {
    $permitidos = ['jpg','jpeg','png','gif','webp'];
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $permitidos)) return false;
    $nome = uniqid() . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], 'uploads/' . $nome)) {
        return $nome;
    }
    return false;
}

// CRUD
$erro = '';
$sucesso = '';

// DELETE
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $stmt = $pdo->prepare("SELECT foto_path FROM sobre WHERE id=?");
    $stmt->execute([$id]);
    $foto = $stmt->fetchColumn();
    $pdo->prepare("DELETE FROM sobre WHERE id=?")->execute([$id]);
    if ($foto && file_exists('uploads/'.$foto)) unlink('uploads/'.$foto);
    $sucesso = "Registro excluído!";
}

// CREATE
if (isset($_POST['acao']) && $_POST['acao'] === 'cadastrar') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $foto = $_FILES['foto'] ?? null;
    if (!$titulo || !$descricao) {
        $erro = "Preencha todos os campos obrigatórios!";
    } else {
        $foto_nome = uploadFoto($foto);
        if ($foto_nome === false) $erro = "Erro no upload da foto!";
        else {
            $stmt = $pdo->prepare("INSERT INTO sobre (titulo, descricao, foto_path) VALUES (?, ?, ?)");
            $stmt->execute([$titulo, $descricao, $foto_nome]);
            $sucesso = "Registro cadastrado!";
        }
    }
}

// UPDATE
if (isset($_POST['acao']) && $_POST['acao'] === 'editar') {
    $id = (int)$_POST['id'];
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $foto = $_FILES['foto'] ?? null;
    if (!$titulo || !$descricao) {
        $erro = "Preencha todos os campos obrigatórios!";
    } else {
        $foto_nome = '';
        if ($foto && $foto['error'] !== UPLOAD_ERR_NO_FILE) {
            $foto_nome = uploadFoto($foto);
            if ($foto_nome === false) $erro = "Erro no upload da foto!";
        }
        if (!$erro) {
            if ($foto_nome) {
                // remove antiga
                $stmt = $pdo->prepare("SELECT foto_path FROM sobre WHERE id=?");
                $stmt->execute([$id]);
                $old_foto = $stmt->fetchColumn();
                if ($old_foto && file_exists('uploads/'.$old_foto)) unlink('uploads/'.$old_foto);
                $stmt = $pdo->prepare("UPDATE sobre SET titulo=?, descricao=?, foto_path=? WHERE id=?");
                $stmt->execute([$titulo, $descricao, $foto_nome, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE sobre SET titulo=?, descricao=? WHERE id=?");
                $stmt->execute([$titulo, $descricao, $id]);
            }
            $sucesso = "Registro atualizado!";
        }
    }
}

// EDIT FORM
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM sobre WHERE id=?");
    $stmt->execute([$id]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($erro) echo "<p style='color:red;'>$erro</p>";
if ($sucesso) echo "<p style='color:green;'>$sucesso</p>";

// FORMULÁRIO
?>
<h2><?php echo isset($edit) ? "Editar Sobre" : "Novo Sobre"; ?></h2>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="acao" value="<?php echo isset($edit) ? "editar" : "cadastrar"; ?>">
    <?php if(isset($edit)): ?>
        <input type="hidden" name="id" value="<?php echo $edit['id']; ?>">
    <?php endif; ?>
    <label>Título*: <input type="text" name="titulo" maxlength="150" value="<?php echo htmlspecialchars($edit['titulo'] ?? ''); ?>"></label><br>
    <label>Descrição*: <br>
        <textarea name="descricao" rows="5" cols="40"><?php echo htmlspecialchars($edit['descricao'] ?? ''); ?></textarea>
    </label><br>
    <label>Foto: <input type="file" name="foto"></label>
    <?php if(isset($edit) && $edit['foto_path']): ?>
        <br>Atual: <img src="uploads/<?php echo htmlspecialchars($edit['foto_path']); ?>" alt="foto" style="max-width:100px;">
    <?php endif; ?>
    <br>
    <button type="submit"><?php echo isset($edit) ? "Atualizar" : "Cadastrar"; ?></button>
</form>

<hr>
<h2>Registros Cadastrados</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Descrição</th>
        <th>Foto</th>
        <th>Criado em</th>
        <th>Ações</th>
    </tr>
<?php
$stmt = $pdo->query("SELECT * FROM sobre ORDER BY created_at DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
    echo "<td>" . nl2br(htmlspecialchars(substr($row['descricao'],0,60))) . "</td>";
    echo "<td>";
    if ($row['foto_path']) {
        echo "<img src='uploads/" . htmlspecialchars($row['foto_path']) . "' style='max-width:60px;'>";
    }
    echo "</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "<td>
        <a href='?edit={$row['id']}'>Editar</a>
        <a href='?del={$row['id']}' onclick=\"return confirm('Excluir?')\">Excluir</a>
    </td>";
    echo "</tr>";
}
?>
</table>
<?php
include 'footer.php';
?>