# Vercel yayını

Uygulama PHP olarak çalışır. `vercel.json`, `api/index.php` üzerinden yalnızca izin verilen sayfa ve işlemleri sunar; PHP runtime sürümü sabitlenmiştir. `/assets` statik dosyaları doğrudan sunulur. Diğer kaynak dosyalar, SQL, testler ve yüklenen dosyalar doğrudan erişime kapalıdır.

Vercel projesine Neon Storage bağlantısı ekleyin. Bağlantı tarafından eklenen `DATABASE_URL_UNPOOLED` (veya `POSTGRES_URL_NON_POOLING`) tercih edilir. Bunlar yoksa `DATABASE_URL` / `POSTGRES_URL` kullanılır. Kimlik bilgilerini Git'e veya arayüz koduna eklemeyin.

İlk istek PostgreSQL'de `posta` şemasını ve tablolarını oluşturur. Veritabanı hesabının şema oluşturma yetkisi gerekir. Yerel MySQL tabloları ve kayıtları otomatik olarak aktarılmaz. Yayındaki kullanıcılar kayıt ekranından yeni hesap oluşturur.

Oturumlar iki saat geçerlilik ve istekler arası kilit ile `posta.sessions` tablosunda tutulur. Dosya ekleri (en çok 2 MB) PostgreSQL'de base64 içerik olarak saklanır; geçici Vercel diskine bağlı değildir. Dosyaları yalnızca mesajın göndereni ve alıcısı indirebilir.

Veritabanı bağlantısı ekledikten veya ortam değişkenlerini değiştirdikten sonra yeniden dağıtım gerekir. GitHub `main` dalına gönderilen değişiklikler Vercel tarafından otomatik yayımlanır.

Yerel XAMPP kullanımı aynıdır: PostgreSQL ortam değişkeni yoksa `config/database.php` içindeki MySQL ayarları ve dosya sistemi kullanılır.

Canlı kontrol: `python tests/http_flow.py https://<alan-adı>` (Python requests gerekir). Bu komut benzersiz test hesapları/mesajları oluşturur ve mevcut kullanıcıları değiştirmez.
