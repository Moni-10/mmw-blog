Website par blog page lagane ke steps
=====================================

Files (is folder mein):
  blogs.php  -> blog list page      (URL: /blog/)
  blog.php   -> single blog page    (URL: /blog/your-slug/)

1) Dono files website ke root folder mein copy karo (jahan index.php / header.php / footer.php hain).
   Local: C:\xampp\htdocs\mmw\

2) .htaccess mein yeh 2 lines add karo. Inhe dusre page-rewrite rules se PEHLE rakho:

     RewriteRule ^blog/?$ blogs.php [L,QSA]
     RewriteRule ^blog/([a-z0-9-]+)/?$ blog.php?slug=$1 [L,QSA,NC]

   (RewriteEngine On pehle se hona chahiye.)

3) Header/footer: dono pages khud header.php aur footer.php include kar lete hain, agar woh same folder mein hon.
   Menu mein BLOG link ko /blog/ par point karo.

4) API address (blog panel ka backend):
   - Live site par default: https://blog-backend.mohindramachine.tech
     Dusra backend ho to server ka environment variable BLOG_API_BASE_URL set karo.
   - Local (localhost) par: http://localhost:5014 apne aap use hota hai.

5) Website ka domain blog panel (admin) ke Websites page mein add hona zaroori hai,
   bilkul usi domain se jis par site chalti hai (www hata ke).
   Local test ke liye domain  localhost/mmw  rakho.

6) Blog "Published" status mein aur usi website ke naam se bana ho, tabhi page par dikhega.

7) Sitemap aap manually add karo: /blog/ aur har blog ka /blog/<slug>/ URL.
