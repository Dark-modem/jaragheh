<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'games';
$pageTitle = 'بازی‌ها';
$uploadDir = __DIR__ . '/../uploads/games';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $sort = (int)($_POST['sort_order'] ?? 0);
        if ($name === '') {
            flash('error', 'نام بازی الزامی است.');
            redirect('admin/games.php');
        }
        $img = handle_image_upload('image', $uploadDir);

        if ($id > 0) {
            if ($img) {
                // حذف تصویر قبلی
                $old = db()->prepare('SELECT image FROM games WHERE id = ?');
                $old->execute([$id]);
                $oldImg = $old->fetchColumn();
                if ($oldImg && file_exists($uploadDir . '/' . $oldImg)) @unlink($uploadDir . '/' . $oldImg);
                db()->prepare('UPDATE games SET name=?, image=?, sort_order=? WHERE id=?')->execute([$name, $img, $sort, $id]);
            } else {
                db()->prepare('UPDATE games SET name=?, sort_order=? WHERE id=?')->execute([$name, $sort, $id]);
            }
            flash('success', 'بازی به‌روزرسانی شد.');
        } else {
            db()->prepare('INSERT INTO games (name, image, sort_order) VALUES (?,?,?)')->execute([$name, $img, $sort]);
            flash('success', 'بازی اضافه شد.');
        }
        redirect('admin/games.php');
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $old = db()->prepare('SELECT image FROM games WHERE id = ?');
        $old->execute([$id]);
        $oldImg = $old->fetchColumn();
        if ($oldImg && file_exists($uploadDir . '/' . $oldImg)) @unlink($uploadDir . '/' . $oldImg);
        db()->prepare('DELETE FROM games WHERE id = ?')->execute([$id]);
        flash('success', 'بازی حذف شد.');
        redirect('admin/games.php');
    }
}

$editId = (int)($_GET['edit'] ?? 0);
$edit = ['id' => 0, 'name' => '', 'image' => '', 'sort_order' => 0];
if ($editId) {
    $s = db()->prepare('SELECT * FROM games WHERE id = ?');
    $s->execute([$editId]);
    if ($row = $s->fetch()) $edit = $row;
}

$games = db()->query('SELECT * FROM games ORDER BY sort_order, id')->fetchAll();

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/admin_header.php';
?>

<div class="page-title">
  <h2>بازی‌های پشتیبانی‌شده</h2>
  <p>این بازی‌ها در بخش اصلی سایت نمایش داده می‌شوند. می‌توانید تصویر و نام هر بازی را تعیین کنید.</p>
</div>

<div class="grid-cols cols-2">
  <!-- فرم -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon($edit['id'] ? 'edit' : 'plus') ?> <?= $edit['id'] ? 'ویرایش بازی' : 'افزودن بازی' ?></h3>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
      <div class="field">
        <label>نام بازی</label>
        <input class="input" name="name" value="<?= e($edit['name']) ?>" required>
      </div>
      <div class="field">
        <label>تصویر بازی</label>
        <input class="input" type="file" name="image" accept="image/*">
        <div class="help">فرمت‌های مجاز: JPG, PNG, WEBP, GIF. نسبت پیشنهادی ۳:۴.</div>
      </div>
      <?php if (!empty($edit['image']) && file_exists($uploadDir . '/' . $edit['image'])): ?>
        <img src="<?= url('uploads/games/' . $edit['image']) ?>" alt="" style="width:90px;border-radius:10px;margin-bottom:1rem">
      <?php endif; ?>
      <div class="field">
        <label>ترتیب نمایش</label>
        <input class="input" type="number" name="sort_order" value="<?= e($edit['sort_order']) ?>" dir="ltr">
      </div>
      <div style="display:flex;gap:.6rem">
        <button class="btn btn-primary" type="submit"><?= icon('check') ?> ذخیره</button>
        <?php if ($edit['id']): ?><a href="<?= url('admin/games.php') ?>" class="btn btn-soft">انصراف</a><?php endif; ?>
      </div>
    </form>
  </div>

  <!-- لیست -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('gamepad') ?> بازی‌های موجود</h3>
    <?php if (!$games): ?>
      <div class="empty" style="padding:1.5rem 0"><?= icon('gamepad') ?><p>هنوز بازی‌ای اضافه نشده.</p></div>
    <?php else: ?>
      <div class="grid-cols" style="grid-template-columns:repeat(2,1fr);gap:.9rem">
        <?php foreach ($games as $g): ?>
          <div style="border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden">
            <div style="aspect-ratio:3/4;background:linear-gradient(160deg,var(--navy),var(--navy-deep));display:grid;place-items:center;position:relative">
              <?php if (!empty($g['image']) && file_exists($uploadDir . '/' . $g['image'])): ?>
                <img src="<?= url('uploads/games/' . $g['image']) ?>" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover">
              <?php else: ?>
                <?= icon('gamepad') ?>
              <?php endif; ?>
            </div>
            <div style="padding:.7rem">
              <b style="font-size:.9rem"><?= e($g['name']) ?></b>
              <div style="display:flex;gap:.4rem;margin-top:.5rem">
                <a href="<?= url('admin/games.php?edit=' . $g['id']) ?>" class="btn btn-soft btn-sm"><?= icon('edit') ?> ویرایش</a>
                <form method="post" style="display:inline" onsubmit="return confirm('حذف این بازی؟')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                  <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash') ?> حذف</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
