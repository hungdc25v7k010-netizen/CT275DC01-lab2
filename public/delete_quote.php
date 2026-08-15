<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xóa một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;

$reason = null;
$quote_details = null;
$delete_complete = false;

if ($has_access) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $quote_id = isset($_POST['id']) && is_numeric($_POST['id'])
            ? (int) $_POST['id']
            : null;

        if (!empty($quote_id)) {
            $query = 'DELETE FROM quotes WHERE id = ?';
            try {
                $pdo = get_database_connection();
                $statement = $pdo->prepare($query);
                $statement->execute([$quote_id]);

                if ($statement->rowCount() == 1) {
                    $delete_complete = true;
                } else {
                    $error_message = 'Không thể xóa trích dẫn này';
                }

            } catch (PDOException $e) {
                $error_message = 'Không thể xóa trích dẫn này';
                $reason = $e->getMessage();
            }

        } else {
            $error_message = 'Không tìm thấy trích dẫn để xóa.';
        }

    } elseif (
        isset($_GET['id']) &&
        is_numeric($_GET['id']) &&
        (int) $_GET['id'] > 0
    ) {

        $quote_id = (int) $_GET['id'];

        $query = 'SELECT id, quote, source, favorite FROM quotes WHERE id = ?';

        try {
            $pdo = get_database_connection();
            $statement = $pdo->prepare($query);
            $statement->execute([$quote_id]);
            $quote_details = $statement->fetch();

            if (!$quote_details) {
                $error_message = 'Không thể lấy trích dẫn này';
            }

        } catch (PDOException $e) {
            $error_message = 'Không thể lấy trích dẫn này';
            $reason = $e->getMessage();

        }

    } else {
        $error_message = 'Không tìm thấy trích dẫn để xóa.';
    }

} else {
    $error_message = 'Bạn không có quyền truy cập trang này';
}
?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Xóa một Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($delete_complete): ?>
    <p>Trích dẫn đã bị xóa.</p>

<?php elseif ($has_access && !$delete_complete && !empty($quote_details)): ?>

    <form action="delete_quote.php" method="post">

        <p>Bạn có chắc là muốn xóa trích dẫn này?</p>

        <div>
            <blockquote><?= html_escape($quote_details['quote']) ?></blockquote>

            <p>
                <?= html_escape($quote_details['source']) ?>

                <?php if (!empty($quote_details['favorite'])): ?>
                    <strong>Yêu thích!</strong>
                <?php endif; ?>
            </p>
        </div>

        <input
            type="hidden"
            name="id"
            value="<?= html_escape((string) $quote_details['id']) ?>"
        >

        <input
            type="submit"
            name="submit"
            value="Xóa Trích dẫn này"
        >

    </form>

<?php endif; ?>
<?php render_page_footer(); ?>