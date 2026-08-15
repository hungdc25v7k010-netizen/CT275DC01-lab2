<?php

define('TITLE', 'Tìm kiếm Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$reason = null;

$keyword = trim($_GET['keyword'] ?? '');
$source = trim($_GET['source'] ?? '');

$sources = [];
$quotes = [];

if ($has_access) {

    try {
        $pdo = get_database_connection();

        $query_sources = '
            SELECT DISTINCT source
            FROM quotes
            WHERE source IS NOT NULL
            AND source <> \'\'
            ORDER BY source
        ';

        $statement = $pdo->query($query_sources);
        $sources = $statement->fetchAll(PDO::FETCH_COLUMN);

        if ($_SERVER['REQUEST_METHOD'] === 'GET'
            && (isset($_GET['keyword']) || isset($_GET['source']))) {

            if ($keyword !== '' && $source !== '') {

                $query = '
                    SELECT id, quote, source, favorite
                    FROM quotes
                    WHERE quote ILIKE ?
                    AND source = ?
                    ORDER BY id
                ';

                $statement = $pdo->prepare($query);
                $statement->execute([
                    '%' . $keyword . '%',
                    $source
                ]);

            } elseif ($keyword !== '') {

                $query = '
                    SELECT id, quote, source, favorite
                    FROM quotes
                    WHERE quote ILIKE ?
                    ORDER BY id
                ';

                $statement = $pdo->prepare($query);
                $statement->execute([
                    '%' . $keyword . '%'
                ]);

            } elseif ($source !== '') {

                $query = '
                    SELECT id, quote, source, favorite
                    FROM quotes
                    WHERE source = ?
                    ORDER BY id
                ';

                $statement = $pdo->prepare($query);
                $statement->execute([
                    $source
                ]);

            } else {

                $query = '
                    SELECT id, quote, source, favorite
                    FROM quotes
                    ORDER BY id
                ';

                $statement = $pdo->query($query);
            }

            $quotes = $statement->fetchAll();
        }

    } catch (PDOException $e) {
        $error_message = 'Không thể thực hiện tìm kiếm.';
        $reason = $e->getMessage();
    }

} else {
    $error_message = 'Bạn không có quyền truy cập trang này';
}

?>

<?php render_page_header(); ?>

<h2>Tìm kiếm Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access): ?>

    <form action="search.php" method="get">

        <p>
            <label>
                Từ khóa:
                <input
                    type="text"
                    name="keyword"
                    value="<?= html_escape($keyword) ?>"
                >
            </label>
        </p>

        <p>
            <label>
                Nguồn/Tác giả:
                <select name="source">
                    <option value="">-- Tất cả nguồn --</option>

                    <?php foreach ($sources as $item): ?>
                        <option
                            value="<?= html_escape($item) ?>"
                            <?= $source === $item ? 'selected' : '' ?>
                        >
                            <?= html_escape($item) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </label>
        </p>

        <p>
            <input
                type="submit"
                name="submit"
                value="Tìm kiếm"
            >
        </p>

    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'GET'
        && (isset($_GET['keyword']) || isset($_GET['source']))): ?>

        <h3>Kết quả tìm kiếm</h3>

        <?php if (!empty($quotes)): ?>

            <?php foreach ($quotes as $quote): ?>

                <div>
                    <blockquote>
                        <?= html_escape($quote['quote']) ?>
                    </blockquote>

                    <p>
                        <?= html_escape($quote['source']) ?>

                        <?php if (!empty($quote['favorite'])): ?>
                            <strong>Yêu thích!</strong>
                        <?php endif; ?>
                    </p>
                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>Không tìm thấy trích dẫn phù hợp.</p>

        <?php endif; ?>

    <?php endif; ?>

<?php endif; ?>

<?php render_page_footer(); ?>