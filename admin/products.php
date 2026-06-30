<?php
require_once __DIR__ . '/../backend/core/helpers.php';
$active = 'products';
$pageTitle = 'محصولات';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id       = (int)($_POST['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $volume   = (int)($_POST['volume_gb'] ?? 0);
        $duration = (int)($_POST['duration_days'] ?? 0);
        $price    = (int)($_POST['price'] ?? 0);
        $discount = $_POST['discount_price'] === '' ? null : (int)$_POST['discount_price'];
        $activeP  = isset($_POST['active']) ? 1 : 0;
        $sort     = (int)($_POST['sort_order'] ?? 0);

        if ($title === '' || $price <= 0) {
            flash('error', 'عنوان و قیمت معتبر الزامی است.');
        } elseif ($discount !== null && $discount >= $price) {
            flash('error', 'قیمت تخفیف‌خورده باید کمتر از قیمت اصلی باشد.');
        } else {
            if ($id > 0) {
                db()->prepare(
                    'UPDATE packages SET title=?, description=?, volume_gb=?, duration_days=?, price=?, discount_price=?, active=?, sort_order=? WHERE id=?'
                )->execute([$title, $desc, $volume, $duration, $price, $discount, $activeP, $sort, $id]);
                flash('success', 'بسته به‌روزرسانی شد.');
            } else {
                db()->prepare(
                    'INSERT INTO packages (title, description, volume_gb, duration_days, price, discount_price, active, sort_order) VALUES (?,?,?,?,?,?,?,?)'
                )->execute([$title, $desc, $volume, $duration, $price, $discount, $activeP, $sort]);
                flash('success', 'بسته جدید اضافه شد.');
            }
        }
        redirect('admin/products.php');
    }

    if ($action === 'delete') {
        db()->prepare('DELETE FROM packages WHERE id = ?')->execute([(int)$_POST['id']]);
        flash('success', 'بسته حذف شد.');
        redirect('admin/products.php');
    }
}

$editId = (int)($_GET['edit'] ?? 0);
$edit = ['id' => 0, 'title' => '', 'description' => '', 'volume_gb' => '', 'duration_days' => 30, 'price' => '', 'discount_price' => '', 'active' => 1, 'sort_order' => 0];
if ($editId) {
    $s = db()->prepare('SELECT * FROM packages WHERE id = ?');
    $s->execute([$editId]);
    if ($row = $s->fetch()) $edit = $row;
}

$packages = db()->query('SELECT * FROM packages ORDER BY sort_order, id')->fetchAll();

require __DIR__ . '/../backend/core/icons.php';
require __DIR__ . '/../frontend/views/inc/admin_header.php';
?>

<div class="page-title">
  <h2>محصولات (بسته‌ها)</h2>
  <p>بسته‌های قابل خرید را اینجا مدیریت کنید. هر بسته می‌تواند قیمت تخفیف‌خورده داشته باشد.</p>
</div>

<div class="grid-cols cols-2">
  <!-- فرم افزودن / ویرایش -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon($edit['id'] ? 'edit' : 'plus') ?> <?= $edit['id'] ? 'ویرایش بسته' : 'بسته جدید' ?></h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
      <div class="field">
        <label>عنوان بسته</label>
        <input class="input" name="title" value="<?= e($edit['title']) ?>" required>
      </div>
      <div class="field">
        <label>توضیحات</label>
        <textarea class="textarea" name="description" style="min-height:70px"><?= e($edit['description']) ?></textarea>
      </div>
      <div class="grid-cols cols-2" style="gap:.8rem">
        <div class="field">
          <label>حجم (گیگابایت)</label>
          <input class="input" type="number" name="volume_gb" value="<?= e($edit['volume_gb']) ?>" dir="ltr">
        </div>
        <div class="field">
          <label>مدت (روز)</label>
          <input class="input" type="number" name="duration_days" value="<?= e($edit['duration_days']) ?>" dir="ltr">
        </div>
        <div class="field">
          <label>قیمت اصلی (تومان)</label>
          <input class="input" type="number" name="price" value="<?= e($edit['price']) ?>" dir="ltr" required>
        </div>
        <div class="field">
          <label>قیمت با تخفیف (اختیاری)</label>
          <input class="input" type="number" name="discount_price" value="<?= e($edit['discount_price']) ?>" dir="ltr" placeholder="خالی = بدون تخفیف">
        </div>
        <div class="field">
          <label>ترتیب نمایش</label>
          <input class="input" type="number" name="sort_order" value="<?= e($edit['sort_order']) ?>" dir="ltr">
        </div>
        <div class="field" style="display:flex;align-items:center;gap:.5rem;margin-top:1.9rem">
          <input type="checkbox" name="active" id="act" <?= $edit['active'] ? 'checked' : '' ?> style="accent-color:var(--spark);width:18px;height:18px">
          <label for="act" style="margin:0">فعال باشد</label>
        </div>
      </div>
      <div style="display:flex;gap:.6rem">
        <button class="btn btn-primary" type="submit"><?= icon('check') ?> ذخیره</button>
        <?php if ($edit['id']): ?><a href="<?= url('admin/products.php') ?>" class="btn btn-soft">انصراف</a><?php endif; ?>
      </div>
    </form>
  </div>

  <!-- لیست بسته‌ها -->
  <div class="card">
    <h3 style="margin-bottom:1rem"><?= icon('box') ?> بسته‌های موجود</h3>
    <?php if (!$packages): ?>
      <div class="empty" style="padding:1.5rem 0"><?= icon('box') ?><p>هنوز بسته‌ای ندارید.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="tbl">
          <thead><tr><th>عنوان</th><th>قیمت</th><th>وضعیت</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($packages as $p): ?>
              <tr>
                <td>
                  <b><?= e($p['title']) ?></b>
                  <div style="font-size:.78rem;color:var(--faint)"><?= fa_num($p['volume_gb']) ?>گیگ • <?= fa_num($p['duration_days']) ?>روز</div>
                </td>
                <td style="white-space:nowrap">
                  <?= toman($p['discount_price'] ?: $p['price']) ?>
                  <?php if ($p['discount_price']): ?><br><span class="off" style="margin:0"><?= toman($p['price']) ?></span><?php endif; ?>
                </td>
                <td><span class="badge <?= $p['active'] ? 'ok' : 'no' ?>"><?= $p['active'] ? 'فعال' : 'غیرفعال' ?></span></td>
                <td style="white-space:nowrap">
                  <a href="<?= url('admin/products.php?edit=' . $p['id']) ?>" class="btn btn-soft btn-sm"><?= icon('edit') ?></a>
                  <form method="post" style="display:inline" onsubmit="return confirm('این بسته حذف شود؟')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash') ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../frontend/views/inc/admin_footer.php'; ?>
