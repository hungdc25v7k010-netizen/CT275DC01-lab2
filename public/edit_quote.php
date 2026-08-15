<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Sửa một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$success_message=null;
$error_message = null;

$reason = null;

$form_data = [
    'id' => null,
    'quote' => '',
    'source' => '',
    'favorite' => false
];

if ($has_access) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $form_data['id'] = isset($_POST['id']) && is_numeric($_POST['id']) ? (int) $_POST['id'] : null;
        $form_data['quote'] = trim($_POST['quote'] ?? '');
        $form_data['source'] = trim($_POST['source'] ?? '');
        $form_data['favorite'] = !empty($_POST['favorite']);

        if (!empty($form_data['id'])) {

            if ($form_data['quote'] !== '' && $form_data['source'] !== '') {

                $query = 'UPDATE quotes SET quote = ?, source = ?, favorite = ? WHERE id = ?';

                try {
                    $pdo = get_database_connection();
                    $statement = $pdo->prepare($query);

                    $statement->bindValue(1, $form_data['quote'], PDO::PARAM_STR);
                    $statement->bindValue(2, $form_data['source'], PDO::PARAM_STR);
                    $statement->bindValue(3, $form_data['favorite'], PDO::PARAM_BOOL);
                    $statement->bindValue(4, $form_data['id'], PDO::PARAM_INT);

                    $statement->execute();

                    if ($statement->rowCount() > 0) {
                        $success_message = 'Trích dẫn này đã được cập nhật.';
                    }

                } catch (PDOException $e) {
                    $error_message = 'Không thể cập nhật Trích dẫn này';
                    $reason = $e->getMessage();
                }

            } else {
                $error_message = 'Hãy gõ vào cả Trích dẫn và Nguồn của nó!';
            }

        } else {
            $error_message = 'Không tìm thấy trích dẫn để sửa.';
        }

    } elseif (isset($_GET['id']) && is_numeric($_GET['id']) && (int) $_GET['id'] > 0) {

        $form_data['id'] = (int) $_GET['id'];

        $query = 'SELECT quote, source, favorite FROM quotes WHERE id = ?';

        try {
            $pdo = get_database_connection();
            $statement = $pdo->prepare($query);
            $statement->execute([$form_data['id']]);

            $row = $statement->fetch();

            if ($row) {
                $form_data['quote'] = $row['quote'];
                $form_data['source'] = $row['source'];
                $form_data['favorite'] = (bool) $row['favorite'];
            } else {
                $error_message = 'Không thể lấy trích dẫn này';
                $form_data['id'] = null;
            }

        } catch (PDOException $e) {
            $error_message = 'Không thể lấy trích dẫn này';
            $reason = $e->getMessage();
            $form_data['id'] = null;
        }

    } else {
        $error_message = 'Không tìm thấy trích dẫn để sửa.';
    }

} else {
    $error_message = 'Bạn không có quyền truy cập trang này';
}

?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Sửa một Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access && !empty($form_data['id'])): ?>

    <?php if (!empty($success_message)): ?>
        <p><?= html_escape($success_message) ?></p>
    <?php endif; ?>

    <form action="edit_quote.php" method="post">

        <p>
            <label>
                Trích dẫn
                <textarea name="quote" rows="5" cols="30"><?= html_escape($form_data['quote']) ?></textarea>
            </label>
        </p>

        <p>
            <label>
                Nguồn
                <input type="text" name="source" value="<?= html_escape($form_data['source']) ?>">
            </label>
        </p>

        <p>
            <label>
                Đây là trích dẫn yêu thích?
                <input type="checkbox" name="favorite" value="yes" <?= $form_data['favorite'] ? 'checked' : '' ?>>
            </label>
        </p>

        <input type="hidden" name="id" value="<?= html_escape((string) $form_data['id']) ?>">

        <p>
            <input type="submit" name="submit" value="Cập nhật Trích dẫn này!">
        </p>

    </form>

<?php endif; ?>

<?php render_page_footer(); ?>