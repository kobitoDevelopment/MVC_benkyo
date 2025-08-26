<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'MVC App'); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            padding-bottom: 60px; /* フッター用の余白 */
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    
    <main>
        <?php 
        // ビューファイルを読み込む
        if (file_exists($view_file)) {
            include $view_file;
        }
        ?>
    </main>
    
    <?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>