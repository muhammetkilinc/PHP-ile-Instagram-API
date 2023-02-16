<?php

$ch = curl_init();

//eğer username değeri gelirse;
if (isset($_GET["username"])) {
    curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.instagram.com/' . $_GET['username'] . '/embed/',
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Linux; Android 6.0.1; SM-G935S Build/MMB29K; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/55.0.2883.91 Mobile Safari/537.36',
    CURLOPT_RETURNTRANSFER => true
  ]);
}



$output = curl_exec($ch);
curl_close($ch);


//değişkenler
$id = 0;
$followers = 0;
$posts = 0;
$username = "";
$fullName = "";
$photo = '';
$story = "Yok";
$gizliHesap = "Gizli Değil";
$dogrulandi = "Doğrulanmadı";
$thumbnail = [];

$tarih = date("Y-m-d-H");
$tamTarih = date("d/m/Y H:i:s");
$kayitliPhoto = "";


//thumbnail listeleme
preg_match_all('@\\\"thumbnail_src\\\":\\\"(.*?)\\\"@i', $output, $result);

if (isset($result[1])) {
  foreach ($result as $item) {
    array_push($thumbnail, str_replace('\\\\\\', '', $item));
  }
}


//id
preg_match('@\\\"owner\\\":{\\\"id\\\":\\\"(.*?)\\\"@i', $output, $result);

if (isset($result[1])) {
  $id = $result[1];
}

//profil fotoğrafı
preg_match('@\\\"profile_pic_url\\\":\\\"(.*?)\\\",\\\"username\\\":\\\"(.*?)\\\"@', $output, $result);

if (isset($result[1])) {
  $photo = str_replace('\\\\\\', '', $result[1]);
}

if (isset($result[2])) {
  $username = $result[2];
}


//bilgiler
preg_match('@\\\"has_public_story\\\":(.*?),\\\"is_private\\\":(.*?),\\\"is_unpublished\\\":(.*?),\\\"is_verified\\\":(.*?),\\\"@i', $output, $result);


if (isset($result[1])) {
  $story = $result[1];
  if ($story == "false") {
    $story = "Yok";
  }
  if ($story == "true") {
    $story = "Var";
  }
}
if (isset($result[2])) {
  $gizliHesap = $result[2];
  if ($gizliHesap == "false") {
    $gizliHesap = "gizli değil";
  }
  if ($gizliHesap == "true") {
    $gizliHesap = "gizli";
  }
}
if (isset($result[4])) {
  $dogrulandi = $result[4];
  if ($dogrulandi == "false") {
    $dogrulandi = "doğrulanmadı";
  }
  if ($dogrulandi == "true") {
    $dogrulandi = "Doğrulandı";
  }
}

if ($story == "true") {
  $story = "Var";
}
if ($gizliHesap == "true") {
  $gizliHesap = "Gizli";
}
if ($dogrulandi == "true") {
  $dogrulandi = "Doğrulandı";
} else if ($story == "false") {
  $story = "Yok";
} else if ($gizliHesap == "false") {
  $gizliHesap = "Gizli Değil";
} else if ($dogrulandi == "false") {
  $dogrulandi = "Doğrulanmadı";
}

//takipçi sayısı
preg_match('@\\\"edge_followed_by\\\":{\\\"count\\\":(.*?)}@i', $output, $result);


if (isset($result[1])) {
  $followers = $result[1];
}


//gönderi sayısı
preg_match('@\\\"edge_owner_to_timeline_media\\\":{\\\"count\\\":(.*?),\\\"edges\\\"@i', $output, $result);


if (isset($result[1])) {
  $posts = $result[1];
}


//kullanıcı tam adı
preg_match('@\\\"full_name\\\":\\\"(.*?)\\\"@i', $output, $result);


if (isset($result[1])) {
  $fullName = $result[1];

}

//klasördeki veriler
$tumunuSay = 0;
$profilSay = 0;
$postSay = 0;
$infoSay = 0;

$profilKlasorBilgi = glob("contents/" . $username . "/profil/*");
$postKlasorBilgi = glob("contents/" . $username . "/post/*");
$infoKlasorBilgi = glob("contents/" . $username . "/info/*");


foreach ($profilKlasorBilgi as $pf) {
  $profilSay += 1;
  $tumunuSay += 1;
}

foreach ($postKlasorBilgi as $pf) {
  $postSay += 1;
  $tumunuSay += 1;
}

foreach ($infoKlasorBilgi as $pf) {
  $infoSay += 1;
  $tumunuSay += 1;
}



if (isset($_GET["username"]) && isset($posts) && $photo) {

  $klasoradi = $username . '';

  if (!file_exists('contents')) {
    mkdir('contents');
  }

  if (!file_exists('contents/' . $klasoradi)) {
    mkdir('contents/' . $klasoradi);
  }

  if (!file_exists('contents/' . $klasoradi . '/profil')) {
    mkdir('contents/' . $klasoradi . '/profil');
  }

  if (!file_exists('contents/' . $klasoradi . '/post')) {
    mkdir('contents/' . $klasoradi . '/post');
  }

  if (!file_exists('contents/' . $klasoradi . '/info')) {
    mkdir('contents/' . $klasoradi . '/info');
  }

  file_put_contents('contents/' . $klasoradi . '/profil/' . $tarih . '.jpg', file_get_contents($photo));
  $kayitliPhoto = $tarih . "";


  //thumbnail
  foreach ($thumbnail[1] as $key => $value) {

    $thumbnailName = str_replace('https://scontent.cdninstagram.com/v/t51.2885-15/', '', $value);
    $parcala = substr($thumbnailName, 0, 9);

    $postTarihi = glob("contents/" . $username . "/post/" . $parcala . ".jpg");

    if (isset($postTarihi)) {
      foreach ($postTarihi as $post) {
        $postDeger = $post;
      }
      @$postDeger = str_replace(
        '.jpg',
        '',
        str_replace('contents/' . $username . '/post/', '', $postDeger)
      );

    }

    if ($postDeger != $parcala) {
      file_put_contents('contents/' . $klasoradi . '/post/' . $parcala . '.jpg', file_get_contents($value));
    }


  }


  //klasörlerinize info.txt adlı bir dosya kaydeder.

  if ($infoSay == 0) {
    $dosya = 'contents/' . $klasoradi . "/info/" . $tarih . ".txt";
    $icerik = 'ID: ' . $id . '
Kullanıcı Adı: ' . $username . '
Tam Adı: ' . $fullName . '
Takipçi Sayısı: ' . $followers . '
Gönderi Sayısı: ' . $posts . '
Hesap: ' . $gizliHesap . '
Verify: ' . $dogrulandi . '
Kayıt Tarihi: ' . $tamTarih . ' ';
    file_put_contents($dosya, $icerik);
  }

  if ($infoSay > 0) {
    $dosya = 'contents/' . $klasoradi . "/info/" . $tarih . ".txt";
    $icerik = 'ID: ' . $id . '
Kullanıcı Adı: ' . $username . '
Tam Adı: ' . $fullName . '
Takipçi Sayısı: ' . $followers . '
Gönderi Sayısı: ' . $posts . '
Hesap: ' . $gizliHesap . '
Verify: ' . $dogrulandi . '
Kayıt Tarihi: ' . $tamTarih . '

Klasör içerisinde ' . $profilSay . ' tane profil, ' . $postSay . ' tane post ve ' . $infoSay . ' tane info dosyası 
olmak üzere toplamda ' . $tumunuSay . ' tane dosya klasörünüze kaydedilmiştir. 
';
    file_put_contents($dosya, $icerik);
  }

}

?>




<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Instagram API - Muhammet KILINÇ</title>
  <link rel="stylesheet" href="index.css" />
  <link rel="SHORTCUT ICON" href="assets/logo/logoSimge.png" />
</head>

<body>
  <nav class="navbar">
    <div class="logoBox">
      <div class="logoBoxLine">
        <a href="index.php">
          <img src="assets/logo/logo.svg" class="logo" />
        </a>
      </div>
    </div>
    <div class="araBox">
      <form action="index.php">
        <input type="text" name="username" class="ara" placeholder="Instagram Kullanıcı Adı ..." />
        <button class="araButon" title="Ara">
          <img src="assets/icons/search.svg" />
        </button>
      </form>
    </div>
    <div class="githubLinkBox">
      <a href="https://github.com/muhammetkilinc" target="_blank">
        <div class="githubLink">
          <img src="assets/icons/github.svg" class="github" />
        </div>
      </a>
    </div>
  </nav>
  <div class="panel">



    <?php

    if (!isset($_GET["username"])) {
      echo '
        <div class="result">Herkese açık bir profili aratın.</div>
            ';
    } else if (!isset($post)) {
      echo '
        <div class="profilError"><a href="https://www.instagram.com/' . $_GET["username"] . '/" style="color:#a12a2a!important;" title="Profili Instagram\'da Gör" target="_blank">
        <b>' . $_GET["username"] . '</b></a> profili gizli hesaptır.</div>
            ';
    }

    if (isset($_GET["username"]) && isset($post)) {


      echo '

      <div class="menu">
        <div class="backColor"></div>
        <div class="profilBox">
          <div class="profilBoxLine">
          ';

      $profil = glob("contents/" . $username . "/profil/" . $tarih . ".jpg");
      foreach ($profil as $pf) {
        echo '<img src="' . $pf . '" class="instaProfil" />';
      }

      echo '
          </div>
          <div class="profilBoxBorder"></div>
        </div>

        <div class="tamAdBox" title="' . $fullName . '">
          <span>' . $fullName . '</span>';

      if ($dogrulandi == "Doğrulandı") {
        echo '
            <img
            src="assets/icons/verify_true.svg"
            class="verifyIcon"
            title="Doğrulandı"
          />
            ';
      }

      echo '
        </div>
        <div class="kullaniciBox">@' . $username . '</div>
        <div class="infoBox">
          <img src="assets/icons/posts.svg" class="gonderi" />
          <span class="gonderiText"><b>' . $posts . '</b> Gönderi</span>
        </div>
        <div class="infoBox">
          <img src="assets/icons/followers.svg" class="gonderi" />
          <span class="gonderiText"><b>' . $followers . '</b> Takipçi</span>
        </div>
        <div class="instagramLinkBox">
          <a
            href="https://www.instagram.com/' . $username . '/"
            target="_blank"
            ><div class="linkBoxLine">Profili Instagram\'da Gör</div></a
          >
        </div>
      </div>

      <div class="icerik">
        <div class="infoBox">
          <div class="id">ID : ' . $id . '</div>
          <a
            href="https://www.instagram.com/' . $username . '/"
            title="Profili Instagram\'da Gör"
            target="_blank"
          >
            <div class="usernameText">
              <b>' . $username . '</b>
              <img src="assets/icons/link.svg" class="link" />
            </div>
          </a>
        </div>

        <div class="fotografBox">
          <div>
            <h4>
              <img src="assets/icons/photos.svg" class="photoIcon" />
              <span>Fotoğraflar (' . $postSay . ')</span>
            </h4>
          </div>

          <div class="fotograflar">

          ';

      $postListele = glob("contents/" . $username . "/post/*");
      foreach ($postListele as $postListele) {
        $fotoNo = str_replace('.jpg', '', str_replace('contents/' . $username . '/post/', '', $postListele));
        echo '
            <div class="fotoLine">
                <a href="' . $postListele . '" title="Resmi Büyütmek İçin Tıklayın">
                    <div class="fotoSinirlayici">
                      <img src="' . $postListele . '" class="foto" />
                    </div>
                </a>
              <a href="' . $postListele . '" title="Resmi Büyütmek İçin Tıklayın">
                <div class="fotoAd">Foto No <b>' . $fotoNo . '</b></div>
              <img src="assets/icons/link.svg" class="fotoLink" />
              </a>
            </div>';
      }


      echo '

          </div>
        </div>
      </div>
      ';


    }




    if ($infoSay == 0 && isset($_GET["username"]) && isset($post)) {
      ?>
      <script>
        window.location.reload();
      </script>
      <?php
    }


    ?>



  </div>
</body>

</html>
