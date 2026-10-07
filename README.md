# Posta

Posta, aynı platforma kayıtlı kullanıcıların birbirine e-posta benzeri mesajlar gönderebildiği PHP ve MariaDB tabanlı bir web uygulamasıdır. Gerçek e-posta servislerine ileti göndermez.

## Gereksinimler

- XAMPP (Apache, PHP 8.1 veya üzeri, MariaDB/MySQL)
- PHP `pdo_mysql`, `mbstring` ve `fileinfo` eklentileri (XAMPP'ta varsayılan olarak bulunur)
- Modern bir web tarayıcısı

## XAMPP kurulumu

1. Bu `mailsystem` klasörünü `C:\xampp\htdocs\mailsystem` konumuna kopyalayın.
2. XAMPP Control Panel'i açın ve **Apache** servisini başlatın.
3. Aynı panelden **MySQL** servisini başlatın.
4. Tarayıcıda [http://localhost/phpmyadmin](http://localhost/phpmyadmin) adresini açın.
5. `mailsystem` adında, `utf8mb4_unicode_ci` karşılaştırmalı yeni bir veritabanı oluşturun. `database.sql` dosyası da veritabanını oluşturabildiği için bu adımı atlayıp doğrudan içe aktarma yapabilirsiniz.
6. phpMyAdmin içindeki **İçe Aktar** bölümünden proje kökündeki `database.sql` dosyasını seçip çalıştırın.
7. XAMPP kurulumunuzun veritabanı bilgileri farklıysa `config/database.php` içindeki `DB_HOST`, `DB_PORT`, `DB_USER` ve `DB_PASSWORD` değerlerini güncelleyin. XAMPP'ın standart ayarları `root` kullanıcısı ve boş şifreyle hazırdır.
8. [http://localhost/mailsystem](http://localhost/mailsystem) adresini açın.

## İlk kullanım ve test akışı

1. Kayıt ekranından `senay` kullanıcı adını ve en az 8 karakterlik bir şifreyi kullanarak ilk hesabı oluşturun.
2. Çıkış yapıp `tuna` kullanıcısını oluşturun.
3. Senay olarak giriş yapın, **Yeni mesaj** seçeneğine girin ve alıcı alanına `tuna` ya da `tuna@mailsystem.local` yazın.
4. Mesajı gönderin. İleti Senay'ın **Gönderilenler** bölümünde görünür.
5. Tuna olarak giriş yapın. İleti **Gelen Kutusu** içinde belirgin, okunmamış biçimde görünür.
6. İletiyi açın. Veritabanındaki `is_read` değeri otomatik olarak `1` olur.

## Klasör yapısı

```text
mailsystem/
├── actions/            Form işlemleri
├── assets/css/         Responsive tasarım
├── assets/js/          Menü, arama ve form etkileşimleri
├── config/             PDO veritabanı bağlantısı
├── includes/           Oturum, güvenlik, yardımcılar ve ortak görünüm
├── compose.php         Yeni mesaj
├── inbox.php           Gelen kutusu
├── message.php         Yetki kontrollü mesaj detayı
├── sent.php            Gönderilenler
├── starred.php         Yıldızlı mesajlar
├── trash.php           Çöp kutusu
└── database.sql        phpMyAdmin kurulum şeması
```

## Güvenlik notları

- Parolalar `password_hash()` ile saklanır, `password_verify()` ile kontrol edilir.
- Veritabanı sorguları PDO prepared statements kullanır.
- Formlarda CSRF doğrulaması, çıkışlarda HTML escaping ve oturum açarken session ID yenileme bulunur.
- Mesaj detayı her istekte gönderen/alıcı yetkisini kontrol eder; URL'deki mesaj numarasını değiştirmek başka kullanıcıların iletilerini açmaz.

## Bu sürümün kapsamı

Bu sürüm kayıt, giriş, gelen kutusu, gönderilenler, mesaj oluşturma, yanıtlama, dosya eki, tekli ve toplu silme, okuma, okunma durumu, yıldızlama ve kullanıcıya özel çöp kutusu akışını içerir. Konuşma zinciri, taslak ve gerçek zamanlı bildirim sonraki aşamalara bırakılmıştır.

## Dil ve JSON etkileşimleri

- Üst bardaki veya giriş/kayıt ekranındaki TR / EN düğmeleri arayüz dilini değiştirir. Tercih PHP session içinde tutulur; çıkış işleminden sonra da aynı oturumda korunur. Kullanıcı adı, adres, konu ve mesaj içeriği çevrilmez.
- Çeviriler `includes/i18n.php` içindedir. Yeni arayüz metinleri `t()` ile eklenir. Dil değişiminde compose metin alanları korunur; seçilmiş dosya yeniden seçilmelidir.
- Yıldızlama `fetch` üzerinden `actions/toggle_star.php` adresine POST gönderir. `Accept: application/json` isteğinde sunucu `{id, is_starred, unread_count}` döndürür. CSRF ve alıcı yetki kontrolü aynen uygulanır. JavaScript kapalıysa normal form yönlendirmesi çalışır.
- `actions/mailbox_status.php` oturum açmış kullanıcıya yalnızca kendi okunmamış mesaj sayısını JSON olarak verir. Görünür sekmelerde 30 saniyede bir ve sekmeye dönüldüğünde sayı güncellenir. Yeni mesaj satırları için Yenile kullanılır.
- Denemek için yıldız düğmesine tıklayın; sayfa yenilenmeden durumu değişir. Aynı hesabın başka sekmesinde okunmamış mesajı açıp ilk sekmeye dönün; sayı güncellenir.
- Geliştirme kontrolleri: `C:\xampp\php\php.exe tests/i18n_json_flow.php` ve `tests/attachment_flow.php` (proje çalışma dizininden). Testler yalnızca kendi oluşturdukları geçici hesapları ve mesajları temizler.

## Dosya ekleri kurulumu ve kullanım

- Mevcut kurulumda phpMyAdmin üzerinden `migrations/001_attachments.sql` dosyasını bir kez içe aktarın. Bu işlem yalnızca `attachments` tablosunu ekler; mevcut kullanıcı ve mesajları silmez. Yeni kurulumlarda `database.sql` yeterlidir.
- Yeni Mesaj formunda isteğe bağlı bir PDF, JPG, PNG veya TXT eklenebilir. Dosya başına sınır 2 MB'dir. Hatalı gönderimden sonra metin alanları korunur; tarayıcı güvenliği nedeniyle dosya yeniden seçilir.
- Mesaj detayındaki dosya bağlantısını yalnızca gönderen ve alıcı indirebilir. Çöp kutusuna taşınan mesajın eki korunur.
- Dosyalar rastgele adlarla `storage/attachments/` altında saklanır. Apache `storage/.htaccess` ile doğrudan erişimi engeller; dosyalar `download.php` üzerinden indirilir. Apache'nin `.htaccess` desteğini açık tutun ve bu klasöre PHP'nin yazabildiğinden emin olun.
- XAMPP `php.ini` içinde `file_uploads=On`, `upload_max_filesize` en az `2M`, `post_max_size` en az `4M` olmalıdır. Standart XAMPP ayarları uygundur. Değişiklik yaparsanız Apache'yi yeniden başlatın.
- Yedekleme yaparken hem veritabanını hem `storage/attachments/` klasörünü alın. Sadece SQL yedeği dosyaların içeriğini kapsamaz.
