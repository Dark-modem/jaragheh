<?php
require_once __DIR__ . '/backend/core/helpers.php';
require_once __DIR__ . '/backend/core/icons.php';

$games = db()->query('SELECT * FROM games ORDER BY sort_order, id')->fetchAll();
$packages = db()->query('SELECT * FROM packages WHERE active = 1 ORDER BY sort_order, id')->fetchAll();

$reviews = [
    ['name' => 'امیر رضایی',   'game' => 'Call of Duty', 'stars' => 5, 'text' => 'برای اولین بار پینگم زیر ۴۰ شد! قبلاً با لگ و پرش بازی می‌کردم، الان اتصال کاملاً پایداره و هیچ افتی ندارم.'],
    ['name' => 'سارا محمدی',   'game' => 'فری فایر',     'stars' => 5, 'text' => 'راه‌اندازیش خیلی ساده بود، بدون تنظیمات پیچیده. پشتیبانی تلگرام هم سریع جواب می‌ده. واقعاً راضی‌ام.'],
    ['name' => 'محمد کریمی',   'game' => 'پابجی موبایل',  'stars' => 4, 'text' => 'چند تا سرویس امتحان کرده بودم ولی کیفیت جرقه فرق داره. تو ساعت‌های شلوغ هم پینگ ثابت می‌مونه.'],
    ['name' => 'نیما احمدی',   'game' => 'فورت نایت',     'stars' => 5, 'text' => 'تیم‌فایت‌ها دیگه با تأخیر نیست، تیرهام درست رجیستر می‌شه. ارزش خریدش رو داشت.'],
    ['name' => 'پارسا یوسفی',  'game' => 'Call of Duty', 'stars' => 5, 'text' => 'سرویس کاملاً ایرانیه و قطعی نداره. از وقتی روی جرقه اومدم رنکم رفت بالا 😄'],
    ['name' => 'الهام نوری',   'game' => 'پابجی موبایل',  'stars' => 4, 'text' => 'قبل از خرید با اکانت تست امتحان کردم و وقتی نتیجه رو دیدم اشتراک گرفتم. پیشنهاد می‌کنم.'],
];

$faqs = [
    ['q' => 'جرقه دقیقاً چه کاری انجام می‌دهد؟', 'a' => 'جرقه با مسیریابی هوشمند داده‌های بازی شما از روی شبکه‌ای از سرورهای بهینه، کوتاه‌ترین و پایدارترین مسیر را تا سرور بازی پیدا می‌کند. نتیجه، کاهش محسوس پینگ و حذف لگ و پرش در بازی‌های آنلاین است.'],
    ['q' => 'آیا این سرویس ایرانی است؟', 'a' => 'بله. جرقه یک سرویس کاملاً ایرانی است؛ پشتیبانی فارسی، پرداخت ریالی از طریق درگاه‌های داخلی، و سرورهایی که به‌طور اختصاصی برای کاربران داخل کشور تنظیم شده‌اند.'],
    ['q' => 'روی چه دستگاه‌هایی کار می‌کند؟', 'a' => 'جرقه روی ویندوز، اندروید و iOS قابل استفاده است و برای کنسول و موبایل هم بهینه شده است. برای راه‌اندازی نیازی به دانش فنی ندارید.'],
    ['q' => 'چطور می‌توانم قبل از خرید مطمئن شوم؟', 'a' => 'پیشنهاد می‌کنیم پیش از خرید اشتراک، از اکانت تست استفاده کنید تا عملکرد سرویس را روی اینترنت خودتان ببینید. برای دریافت تست به پشتیبانی پیام دهید.'],
    ['q' => 'پرداخت چگونه انجام می‌شود؟', 'a' => 'دو روش دارید: پرداخت آنلاین از طریق درگاه بانکی، یا کارت‌به‌کارت با هماهنگی پشتیبانی تلگرام. پس از پرداخت، اشتراک شما بلافاصله فعال می‌شود.'],
    ['q' => 'اگر به مشکل بخورم چه کنم؟', 'a' => 'پشتیبانی جرقه از طریق سامانهٔ تیکت در پنل کاربری و همچنین تلگرام پاسخگوی شماست. تیم فنی ما برای رفع هر مشکلی همراه شماست.'],
];

$pageTitle = 'بهینه‌ساز شبکه مخصوص گیم';
$showEnamad = true;  // نمایش نماد اعتماد در فقط صفحه اصلی
require __DIR__ . '/frontend/views/inc/header.php';
?>

<!-- ===================== هیرو ===================== -->
<section class="hero" id="home">
  <div class="container grid-2">
    <div>
      <span class="eyebrow"><?= icon('zap') ?> پینگ کمتر، برد بیشتر</span>
      <h1>اتصالی که در <span class="hl">میدان نبرد</span> تفاوت می‌سازد.</h1>
      <p class="lead">جرقه با شبکه‌ای از سرورهای بهینهٔ ایرانی، مسیر داده‌های بازی شما را کوتاه می‌کند تا پینگ پایین و اتصال پایدار را تجربه کنید؛ بدون لگ، بدون افت، بدون تنظیمات پیچیده.</p>
      <div class="hero-cta">
        <a href="<?= url('register.php') ?>" class="btn btn-primary btn-lg"><?= icon('spark') ?> شروع کنید</a>
        <a href="#packages" class="btn btn-ghost btn-lg">مشاهده بسته‌ها</a>
      </div>
      <div class="hero-stats">
        <div class="stat"><b><?= fa_num('۲٬۰۰۰+') ?></b><span>کاربر فعال ماهانه</span></div>
        <div class="stat"><b><?= fa_num(6) ?></b><span>سرور بهینه</span></div>
        <div class="stat"><b><?= fa_num(4) ?></b><span>بازی پشتیبانی‌شده</span></div>
      </div>
    </div>

    <div>
      <div class="ping-card">
        <div class="ping-head">
          <div>
            <div class="label">پینگ شما با جرقه</div>
            <div class="ping-val"><?= fa_num(26) ?> <small>ms</small></div>
          </div>
          <span class="badge ok"><span class="live-dot"></span> آنلاین</span>
        </div>
        <svg class="js-ping ping-graph"></svg>
        <div class="ping-legend">
          <span>قبل از جرقه: <?= fa_num(92) ?>ms</span>
          <span>کاهش <?= fa_num('۷۱٪') ?> پینگ</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== نوار اعتماد ===================== -->
<div class="trustbar">
  <div class="container">
    <div class="trust-item"><?= icon('flag') ?> <span>سرویس <b>کاملاً ایرانی</b></span></div>
    <div class="trust-item"><?= icon('wallet') ?> <span>پرداخت <b>امن ریالی</b></span></div>
    <div class="trust-item"><?= icon('headset') ?> <span>پشتیبانی <b>فارسی ۲۴ ساعته</b></span></div>
    <div class="trust-item"><?= icon('rocket') ?> <span>فعال‌سازی <b>آنی اشتراک</b></span></div>
    <div class="trust-item"><?= icon('verified') ?> <span>اعتماد <b>۲٬۰۰۰+ گیمر</b></span></div>
  </div>
</div>

<!-- ===================== بازی‌ها ===================== -->
<section class="section" id="games">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('gamepad') ?> پشتیبانی اختصاصی</span>
      <h2>بازی‌های پشتیبانی‌شده</h2>
      <p>مسیر بهینه برای محبوب‌ترین عناوین رقابتی، تنظیم‌شده روی شش سرور جرقه.</p>
    </div>
    <div class="games-grid">
      <?php foreach ($games as $g): ?>
        <div class="game-card">
          <?php if (!empty($g['image']) && file_exists(__DIR__ . '/uploads/games/' . $g['image'])): ?>
            <div class="img" style="background-image:url('<?= url('uploads/games/' . $g['image']) ?>')"></div>
          <?php else: ?>
            <div class="ph"><?= icon('gamepad') ?></div>
          <?php endif; ?>
          <div class="label"><?= e($g['name']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== ویژگی‌ها ===================== -->
<section class="section" id="features">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('shield') ?> چرا جرقه؟</span>
      <h2>ساخته‌شده برای گیمرهای ایرانی</h2>
    </div>
    <div class="features">
      <div class="card feature">
        <div class="ico"><?= icon('gauge') ?></div>
        <h3>کاهش واقعی پینگ</h3>
        <p>مسیریابی هوشمند داده‌ها روی نزدیک‌ترین و سریع‌ترین سرور تا تأخیر را به حداقل برساند.</p>
      </div>
      <div class="card feature">
        <div class="ico"><?= icon('server') ?></div>
        <h3>شش سرور پرسرعت</h3>
        <p>زیرساخت توزیع‌شده با ظرفیت بالا که حتی در ساعات شلوغ هم پایدار می‌ماند.</p>
      </div>
      <div class="card feature">
        <div class="ico"><?= icon('shield') ?></div>
        <h3>اتصال امن و پایدار</h3>
        <p>ارتباط رمزگذاری‌شده و باثبات تا قطعی و افت ناگهانی، بازی شما را خراب نکند.</p>
      </div>
      <div class="card feature">
        <div class="ico"><?= icon('flag') ?></div>
        <h3>کاملاً ایرانی</h3>
        <p>پشتیبانی فارسی، پرداخت ریالی و سرورهایی که برای اینترنت داخل کشور تنظیم شده‌اند.</p>
      </div>
      <div class="card feature">
        <div class="ico"><?= icon('rocket') ?></div>
        <h3>راه‌اندازی ساده</h3>
        <p>بدون نیاز به دانش فنی؛ ثبت‌نام کنید، اشتراک بگیرید و در چند دقیقه متصل شوید.</p>
      </div>
      <div class="card feature">
        <div class="ico"><?= icon('headset') ?></div>
        <h3>پشتیبانی همیشگی</h3>
        <p>تیم پشتیبانی از طریق تیکت و تلگرام همیشه کنار شماست تا بازی بدون دغدغه باشد.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===================== با بقیه فرق داریم ===================== -->
<section class="section" id="why-different">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('star') ?> تفاوت اصلی</span>
      <h2>با بقیه فرق داریم</h2>
      <p>سرویس کاهش پینگ طراحی‌شده برای گیمرهای ایرانی</p>
    </div>
    <div class="why-grid">

      <div class="card why-card">
        <div class="why-icon"><?= icon('gauge') ?></div>
        <div class="why-body">
          <h3>پینگ فوق پایین</h3>
          <p>سرورهای بهینه با مسیریابی هوشمند. پینگ زیر ۳۰ms در اکثر بازی‌ها.</p>
        </div>
      </div>

      <div class="card why-card">
        <div class="why-icon"><?= icon('gamepad') ?></div>
        <div class="why-body">
          <h3>مخصوص گیمینگ</h3>
          <p>فقط ترافیک بازی از مسیر بهینه رد میشه. بقیه ترافیکت دست نخورده می‌مونه.</p>
        </div>
      </div>

      <div class="card why-card">
        <div class="why-icon"><?= icon('flag') ?></div>
        <div class="why-body">
          <h3>سرویس ایرانی</h3>
          <p>زیرساخت داخلی. داده‌ات پیش ما می‌مونه. بدون سرورهای خارجی.</p>
        </div>
      </div>

      <div class="card why-card">
        <div class="why-icon"><?= icon('rocket') ?></div>
        <div class="why-body">
          <h3>اپ اختصاصی</h3>
          <p>اپلیکیشن مخصوص Windows، iOS و Android. راه‌اندازی یه‌کلیکه.</p>
        </div>
      </div>

      <div class="card why-card">
        <div class="why-icon"><?= icon('server') ?></div>
        <div class="why-body">
          <h3>آپتایم ۹۹.۹٪</h3>
          <p>سرورهایی که هیچ‌وقت خاموش نمیشن. هر وقت بازی کنی، ما اینجاییم.</p>
        </div>
      </div>

      <div class="card why-card">
        <div class="why-icon"><?= icon('headset') ?></div>
        <div class="why-body">
          <h3>پشتیبانی ۲۴/۷</h3>
          <p>مشکل داری؟ تیم ما همیشه آماده‌ست. تلگرام، ایمیل، هر وقت بخوای.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ===================== پلتفرم‌ها ===================== -->
<section class="section" id="platforms" style="padding-top:0">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('globe') ?> همه‌جا، روی هر دستگاه</span>
      <h2>روی دستگاه دلخواه شما</h2>
    </div>
    <div class="platforms">
      <div class="platform"><?= icon('windows') ?> ویندوز</div>
      <div class="platform"><?= icon('android') ?> اندروید</div>
      <div class="platform"><?= icon('apple') ?> iOS</div>
      <div class="platform"><?= icon('gamepad') ?> کنسول و موبایل</div>
    </div>
  </div>
</section>

<!-- ===================== بسته‌ها ===================== -->
<section class="section" id="packages">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('wallet') ?> تعرفه‌ها</span>
      <h2>بسته‌ای مناسب هر گیمر</h2>
      <p>برای خرید و فعال‌سازی، وارد حساب کاربری شوید.</p>
    </div>
    <div class="pkg-grid">
      <?php foreach ($packages as $i => $p):
        $hasOff = !empty($p['discount_price']) && $p['discount_price'] < $p['price'];
        $final = $hasOff ? $p['discount_price'] : $p['price'];
        $off = $hasOff ? round((1 - $p['discount_price'] / $p['price']) * 100) : 0;
        $featured = $i === 1;
      ?>
        <div class="card pkg <?= $featured ? 'featured' : '' ?>">
          <?php if ($featured): ?><span class="ribbon">پیشنهاد ویژه</span><?php endif; ?>
          <h3><?= e($p['title']) ?></h3>
          <div class="desc"><?= e($p['description']) ?></div>
          <div class="price">
            <span class="now"><?= toman($final) ?></span>
            <?php if ($hasOff): ?><span class="old"><?= toman($p['price']) ?></span><?php endif; ?>
          </div>
          <?php if ($hasOff): ?><span class="off"><?= fa_num($off) ?>٪ تخفیف</span><?php endif; ?>
          <ul>
            <li><?= icon('database') ?> حجم <?= fa_num($p['volume_gb']) ?> گیگابایت</li>
            <li><?= icon('clock') ?> اعتبار <?= fa_num($p['duration_days']) ?> روزه</li>
            <li><?= icon('server') ?> دسترسی به هر ۶ سرور</li>
            <li><?= icon('check') ?> پشتیبانی تیکت</li>
          </ul>
          <a href="<?= url('panel/buy.php') ?>" class="btn btn-primary btn-block">خرید بسته</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== تست رایگان ===================== -->
<section class="section" id="freetest" style="padding-top:0">
  <div class="container">
    <div class="cta-banner">
      <span class="eyebrow"><?= icon('gift') ?> پیش از خرید امتحان کنید</span>
      <h2>اول تست کنید، بعد خرید کنید</h2>
      <p>پیشنهاد می‌کنیم قبل از تهیهٔ اشتراک، با اکانت تست عملکرد جرقه را روی اینترنت خودتان ببینید. وقتی از نتیجه مطمئن شدید، اشتراک بگیرید.</p>
      <div class="hero-cta">
        <a href="<?= e(setting('telegram_id', 'https://t.me/supjaragheh')) ?>" target="_blank" rel="noopener" class="btn btn-primary"><?= icon('tg') ?> دریافت اکانت تست</a>
        <a href="<?= url('register.php') ?>" class="btn btn-ghost">ساخت حساب</a>
      </div>
    </div>
  </div>
</section>

<!-- ===================== نظرات کاربران ===================== -->
<section class="section" id="reviews">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('star') ?> رضایت گیمرها</span>
      <h2>کاربران ما چه می‌گویند</h2>
      <p>تجربهٔ واقعی گیمرهایی که با جرقه بازی می‌کنند.</p>
    </div>
    <div class="reviews-grid">
      <?php foreach ($reviews as $r): ?>
        <div class="review">
          <span class="quote-mark"><?= icon('quote') ?></span>
          <span class="stars">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <?= str_replace('icn', 'icn' . ($s <= $r['stars'] ? '' : ' off'), icon('star')) ?>
            <?php endfor; ?>
          </span>
          <p><?= e($r['text']) ?></p>
          <div class="who">
            <span class="av"><?= e(mb_substr($r['name'], 0, 1, 'UTF-8')) ?></span>
            <span>
              <span class="nm"><?= e($r['name']) ?></span><br>
              <span class="meta"><?= e($r['game']) ?></span>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== سوالات متداول ===================== -->
<section class="section" id="faq">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?= icon('help') ?> پرسش و پاسخ</span>
      <h2>سوالات متداول</h2>
    </div>
    <div class="faq-wrap">
      <?php foreach ($faqs as $i => $f): ?>
        <div class="faq-item <?= $i === 0 ? 'open' : '' ?>">
          <button type="button" class="faq-q"><span><?= e($f['q']) ?></span> <?= icon('chevron') ?></button>
          <div class="faq-a"><div class="inner"><?= e($f['a']) ?></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/frontend/views/inc/footer.php'; ?>
