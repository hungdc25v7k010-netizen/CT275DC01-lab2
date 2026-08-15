<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Thêm một Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$sucess_message=null;
$error_message = null;

$reason = null;

$form_data = [
    'quote' => trim($_POST['quote'] ?? ''),
    'source' => trim($_POST['source'] ?? ''),
    'favorite' => !empty($_POST['favorite'])
];

if ($has_access && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($form_data['quote'] !== '' && $form_data['source'] !== '') {

        $query = 'INSERT INTO quotes (quote, source, favorite) VALUES (?, ?, ?)';

        try {
            $pdo = get_database_connection();
            $statement = $pdo->prepare($query);
            $statement->bindValue(1, $form_data['quote'], PDO::PARAM_STR);
            $statement->bindValue(2, $form_data['source'], PDO::PARAM_STR);
            $statement->bindValue(3, $form_data['favorite'], PDO::PARAM_BOOL);
            $statement->execute();

            if ($statement->rowCount() === 1) {
                $success_message = 'Trích dẫn của bạn đã được lưu trữ.';
                $form_data = ['quote' => '', 'source' => '', 'favorite' => false];
            } else {
                $error_message = 'Không thể lưu trữ trích dẫn';
            }
        } catch (PDOException $e) {
            $error_message = 'Không thể lưu trữ trích dẫn';
            $reason = $e->getMessage();
        }

    } else {
        $error_message = 'Hãy gõ vào cả Trích dẫn và Nguồn của nó!';
    }

} elseif (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
}

?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Thêm một Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>


<?php if ($has_access): ?>
    <?php if (!empty($success_message)): ?>
    <p><?= html_escape($success_message) ?></p>
    <?php endif; ?>
    <form action="add_quote.php" method="post">
        <p>
            <label>Trích dẫn
                <textarea name="quote" rows="5" cols="30"><?= html_escape($form_data['quote']) ?></textarea>
            </label>
        </p>
        <p>
            <label>Nguồn
               <input type="text" name="source" value="<?= html_escape($form_data['source']) ?>">
            </label>
        </p>
        <p>
            <label>Đây là trích dẫn yêu thích?
                <input type="checkbox" name="favorite" value="yes" <?= $form_data['favorite'] ? 'checked' : '' ?>>
            </label>
        </p>
        <p><input type="submit" name="submit" value="Thêm Trích dẫn này!"></p>
    </form>
<?php endif; ?>

<?php render_page_footer(); ?>