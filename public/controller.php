<?php
const HOST = '127.0.0.1';  // use localhost TCP (container runs both PHP + MariaDB)
const DBNAME = 'test_database';
const USER = 'appuser';
const PASSWORD = 'apppass'; // match MYSQL_ROOT_PASSWORD from Dockerfile

try {
    $pdo = new PDO("mysql:host=" . HOST . ";dbname=" . DBNAME . ";charset=utf8mb4", USER, PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper: get first available key from array
function get_first(array $arr, array $keys, $default = '') {
    foreach ($keys as $k) {
        if (isset($arr[$k])) return $arr[$k];
    }
    return $default;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $marka = get_first($_POST, ['brand','marka'], '');
        $model = get_first($_POST, ['model'], '');
        $rok   = get_first($_POST, ['year','rok'], null);
        $stmt = $pdo->prepare("INSERT INTO auta (marka, model, rok) VALUES (?, ?, ?)");
        $stmt->execute([$marka, $model, $rok]);
    } elseif (isset($_POST['edit'])) {
        $id    = $_POST['id'] ?? null;
        $marka = get_first($_POST, ['brand','marka'], '');
        $model = get_first($_POST, ['model'], '');
        $rok   = get_first($_POST, ['year','rok'], null);
        $stmt = $pdo->prepare("UPDATE auta SET marka = ?, model = ?, rok = ? WHERE id = ?");
        $stmt->execute([$marka, $model, $rok, $id]);
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'] ?? null;
        $stmt = $pdo->prepare("DELETE FROM auta WHERE id = ?");
        $stmt->execute([$id]);
    } elseif (isset($_POST['drop_all'])) {
        $pdo->exec("TRUNCATE TABLE auta");
    }
}

$stmt = $pdo->query("SELECT * FROM auta");
$allCars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modyfikacja Tabeli Auta</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="normalize.css">
    <style>
        table {
            border-collapse: collapse;
            width: 80%;
            margin-top: 20px;
            text-align: center;
            font-size: 1rem;
        }

        table th, table td {
            padding: 10px;
        }

        table th {
            border-bottom: 2px solid #0F0F0F;
        }

        table td {
            border-bottom: 1px solid #ccc;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }

        table td:nth-child(5) button {
            padding: 3px 7px;
            background: none;
            border: none;
            border-bottom: 1px solid transparent;
            cursor: pointer;
            transition: 200ms;
        }

        table td:nth-child(5) button:nth-child(1):hover {
            border-bottom: 1px solid #791E94;
        }

        table td:nth-child(5) button:nth-child(2):hover {
            border-bottom: 1px solid #b41414;
        }
    </style>
</head>
<body>
    <h1>Tabela Auta</h1>

    <!-- Add Car -->
    <h2>Dodaj Auto</h2>
    <form method="POST">
        <input type="text" name="brand" placeholder="Marka" required>
        <input type="text" name="model" placeholder="Model" required>
        <input type="number" name="year" placeholder="Rok" required min="1886" max="2026">
        <button type="submit" name="add">Dodaj</button>
    </form>

    <form method="POST">
        <h2>UWAGA!</h2>
        <button type="submit" name="drop_all" onclick="return confirm('Jesteś pewien czy usunąć wszystkie rekordy?')">Usuń wszystkie rekordy</button>
    </form>

    <h2>Auta w Tabeli:</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Marka</th>
                <th>Model</th>
                <th>Rok</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allCars as $car): ?>
                <tr>
                    <td><?php echo $car['id']; ?></td>
                    <td><?php echo htmlspecialchars($car['marka'] ?? $car['brand'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($car['model'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($car['rok'] ?? $car['year'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $car['id']; ?>">
                            <input type="text" name="brand" value="<?php echo htmlspecialchars($car['marka'] ?? $car['brand'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            <input type="text" name="model" value="<?php echo htmlspecialchars($car['model'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            <input type="number" name="year" value="<?php echo htmlspecialchars($car['rok'] ?? $car['year'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            <button type="submit" name="edit">Edytuj</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $car['id']; ?>">
                            <button type="submit" name="delete" onclick="return confirm('Are you sure?')">Usuń</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>