# Posta

Posta, aynı platforma kayıtlı kullanıcıların birbirine e-posta benzeri mesajlar gönderebildiği PHP ve MariaDB tabanlı bir web uygulamasıdır. Gerçek e-posta servislerine ileti göndermez.

## Gereksinimler

- XAMPP (Apache, PHP 8.1 veya üzeri, MariaDB/MySQL)
- PHP `pdo_mysql` ve `mbstring` eklentileri (XAMPP'ta varsayılan olarak bulunur)
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

Bu sürüm kayıt, giriş, gelen kutusu, gönderilenler, mesaj oluşturma, yanıtlama, tekli ve toplu silme, okuma, okunma durumu, yıldızlama ve kullanıcıya özel çöp kutusu akışını içerir. Konuşma zinciri, taslak, ek dosya ve gerçek zamanlı bildirim sonraki aşamalara bırakılmıştır.
