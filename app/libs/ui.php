<?php 
class Ui {
    public static function pageing($dataHalaman, $dataRecord, $limit = 10, $deep_params = null, $controller = null) {
        if (is_null($controller)) {
            $controller = App::getRouter()->getController();
            $action = App::getRouter()->getAction();
            $params = App::getRouter()->getParams();
            if ($action == 'index') {
                $action = 'halaman';
            } else {
                if (count($params) && ctype_digit(end($params))) {
                    $removed = array_pop($params);
                }
            }
        }

        $batas_halaman = ceil($dataRecord/$limit); 

        $jumlah_halaman = $batas_halaman;

        if ($batas_halaman > 3) {
            $jumlah_halaman = 3;
        }

        if ($batas_halaman < 1) { 
            $jumlah_halaman = 1; 
            $batas_halaman = 1;
            $prefHref = '';
            $nextHref = '';
        }

        if ($dataHalaman > 1 && $dataHalaman < $batas_halaman) {
            $y = $dataHalaman - 1;
            $jumlah_halaman = $dataHalaman + 1;
            $prevHref = $y;
            $nextHref = $jumlah_halaman;
        } elseif ($dataHalaman == $batas_halaman && $batas_halaman > 2) {
            $y = $dataHalaman - 2;
            $jumlah_halaman = $batas_halaman;
            $prevHref = $y+1;
            $nextHref = '';
        } elseif ($dataHalaman == 1 && $jumlah_halaman > 1) {
            $y = 1;
            $prevHref = '';
            $nextHref = $dataHalaman+1;
        } else {
            $prevHref = $dataHalaman - 1;
            if ($prevHref == 0) {
                $prevHref = '';
            }
            $nextHref = '';
            $y = 1;
        }
        $button = '<li class="page-item '. (($dataHalaman == 1) ? 'disabled':'') .'">
                        <a class="page-link" href="/admin/'. $controller .'/'. $action . '/'. ((count($params)) ? implode('/', $params) : (!is_null($deep_params) ? $deep_params .'/' : '') . $prevHref ).'" tabindex="-1">
                            <i class="fas fa-angle-left"></i>
                            <span class="sr-only">Previous</span>
                        </a>
                    </li>';
        for ($x = $y; $x <= $jumlah_halaman; $x++) {
            $button .= '<li class="page-item '. (($dataHalaman == $x) ? 'active' : '') .'">
                            <a class="page-link" href="/admin/'. $controller .'/'. $action . '/'. ((count($params)) ? implode('/', $params).'/'.$x : (!is_null($deep_params) ? $deep_params .'/' : '') . $x) .'">'. $x .'</a>
                        </li>';
        }
        $button .= '<li class="page-item '. (($dataHalaman == $batas_halaman) ? 'disabled':'') .'">
                        <a class="page-link" href="/admin/'. $controller .'/'. $action . '/'. ((count($params)) ? implode('/', $params) : (!is_null($deep_params) ? $deep_params .'/' : '') . $nextHref) .'">
                            <i class="fas fa-angle-right"></i>
                            <span class="sr-only">Next</span>
                        </a>
                    </li>';
        return $button;
    }
    
    // public static function pageing($dataHalaman, $dataRecord, $links, $key, $limit = 10, $deep_params = null, $controller = null) {
    //     if (is_null($controller)) {
    //         $controller = App::getRouter()->getController();
    //         $action = App::getRouter()->getAction();
    //         $params = App::getRouter()->getParams();
    //         if ($action == 'index') {
    //             $action = 'halaman';
    //         } else {
    //             if (count($params) && ctype_digit(end($params))) {
    //                 $removed = array_pop($params);
    //             }
    //         }
    //     }

    //     $batas_halaman = ceil($dataRecord/$limit); 

    //     $jumlah_halaman = $batas_halaman;

    //     if ($batas_halaman > 3) {
    //         $jumlah_halaman = 3;
    //     }

    //     if ($batas_halaman < 1) { 
    //         $jumlah_halaman = 1; 
    //         $batas_halaman = 1;
    //         $prefHref = '';
    //         $nextHref = '';
    //     }

    //     if ($dataHalaman > 1 && $dataHalaman < $batas_halaman) {
    //         $y = $dataHalaman - 1;
    //         $jumlah_halaman = $dataHalaman + 1;
    //         $prevHref = $y;
    //         $nextHref = $jumlah_halaman;
    //     } elseif ($dataHalaman == $batas_halaman && $batas_halaman > 2) {
    //         $y = $dataHalaman - 2;
    //         $jumlah_halaman = $batas_halaman;
    //         $prevHref = $y+1;
    //         $nextHref = '';
    //     } elseif ($dataHalaman == 1 && $jumlah_halaman > 1) {
    //         $y = 1;
    //         $prevHref = '';
    //         $nextHref = $dataHalaman+1;
    //     } else {
    //         $prevHref = $dataHalaman - 1;
    //         if ($prevHref == 0) {
    //             $prevHref = '';
    //         }
    //         $nextHref = '';
    //         $y = 1;
    //     }
    //     $button = '<li class="page-item '. (($dataHalaman == 1) ? 'disabled':'') .'">
    //                     <a class="page-link" href="/admin/'. $controller .'/'. $action . '/'. ((count($params)) ? implode('/', $params) : (!is_null($deep_params) ? $deep_params .'/' : '') . $prevHref ).'" tabindex="-1">
    //                         <i class="fas fa-angle-left"></i>
    //                         <span class="sr-only">Previous</span>
    //                     </a>
    //                 </li>';
    //     for ($x = $y; $x <= $jumlah_halaman; $x++) {
    //         $button .= '<li class="page-item '. (($dataHalaman == $x) ? 'active' : '') .'">
    //                         <a class="page-link" href="/admin/'. $controller .'/'. $action . '/'. ((count($params)) ? implode('/', $params).'/'.$x : (!is_null($deep_params) ? $deep_params .'/' : '') . $links[$x-1][$key]) .'">'. $x .'</a>
    //                     </li>';
    //     }
    //     $button .= '<li class="page-item '. (($dataHalaman == $batas_halaman) ? 'disabled':'') .'">
    //                     <a class="page-link" href="/admin/'. $controller .'/'. $action . '/'. ((count($params)) ? implode('/', $params) : (!is_null($deep_params) ? $deep_params .'/' : '') . $nextHref) .'">
    //                         <i class="fas fa-angle-right"></i>
    //                         <span class="sr-only">Next</span>
    //                     </a>
    //                 </li>';
    //     return $button;
    // }

    public static function tableIsNull($td_length, $text) {
        echo '<tr><td colspan="' . $td_length . '"> Data ' . $text . ' Tidak Ditemukan </td></tr>';
    }

    public static function headerProfil($data) {
        echo '<!-- Header Default -->
        <div class="header pb-6 d-flex align-items-center"
            style="min-height: 500px; background-image: url(/assets/images/default-cover.jpg); background-size: cover; background-position: center top;">
            <!-- Mask -->
            <span class="mask bg-gradient-default opacity-8"></span>
            <!-- Header container -->
            <div class="container-fluid d-flex align-items-center">
                <div class="row">
                    <div class="col-lg-7 col-md-10">
                        <h1 class="display-2 text-white">'.$data .'</h1>
                        <p class="text-white mt-0 mb-5">Penggantian akun email dan password pastikan menggunakan alamat email yang aktif.</p>
                    </div>
                </div>
            </div>
        </div>';
    }

    public static function headerProfilOrgin($data) {
        echo '<!-- Header Default -->
        <div class="header pb-6 d-flex align-items-center"
            style="min-height: 500px; background-image: url(/assets/images/default-cover.jpg); background-size: cover; background-position: center top;">
            <!-- Mask -->
            <span class="mask bg-gradient-default opacity-8"></span>
            <!-- Header container -->
            <div class="container-fluid d-flex align-items-center">
                <div class="row">
                    <div class="col-lg-7 col-md-10">
                        <h1 class="display-2 text-white">'.$data .'</h1>
                        <p class="text-white mt-0 mb-5">Penggantian akun email dan password pastikan menggunakan alamat email yang aktif.</p>
                        <a href="#" class="btn btn-neutral">Ganti Gambar</a>
                    </div>
                </div>
            </div>
        </div>';
    }

    public static function headerDefault($route) {
        echo '<!-- Header Profile -->
        <div class="header bg-primary pb-6">
            <div class="container-fluid">
                <div class="header-body">
                    <div class="row align-items-center py-4">
                        <!-- Page Title & Breadcrumb -->
                        <div class="col-lg-8 col-8">
                            <h6 class="h2 text-white d-inline-block mb-0">'. ucfirst($route) .'</h6>
                            <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                                <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                                    '. Breadcrumb::generate() .'
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
<<<<<<< HEAD

=======
    
    // Baru dari sini
>>>>>>> f611ab7aefc8a1db8f9fd2871435bab3676fbee5
    public static function emailNotifDonasiDonatur($params = array()) {
        if (isset($params)) {
            return '<!doctype html>
                    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
                    <head>
                        <title>
                        Notif Donasi Donatur
                        </title>
                        <!--[if !mso]><!-- -->
                        <meta http-equiv="X-UA-Compatible" content="IE=edge">
                        <!--<![endif]-->
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <style type="text/css">
                            #outlook a { padding:0; }
                            body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
                            table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
                            img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
                            p { display:block;margin:13px 0; }
                        </style>
                        <!--[if mso]>
                        <xml>
                        <o:OfficeDocumentSettings>
                        <o:AllowPNG/>
                        <o:PixelsPerInch>96</o:PixelsPerInch>
                        </o:OfficeDocumentSettings>
                        </xml>
                        <![endif]-->
                        <!--[if lte mso 11]>
                        <style type="text/css">
                        .outlook-group-fix { width:100% !important; }
                        </style>
                        <![endif]-->
                        
                    <!--[if !mso]><!-->
                        <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
                        <link href="https://fonts.googleapis.com/css?family=Cabin:400,700" rel="stylesheet" type="text/css">
                        <style type="text/css">
                            @import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
                            @import url(https://fonts.googleapis.com/css?family=Cabin:400,700);
                        </style>
                    <!--<![endif]-->
    
                    <style type="text/css">
                        @media only screen and (max-width:480px) {
                            .mj-column-per-100 { width:100% !important; max-width: 100%; }
                            .mj-column-per-50 { width:50% !important; max-width: 50%; }
                        }
                    </style>
    
                    <style type="text/css">
                        @media only screen and (max-width:480px) {
                            table.full-width-mobile { width: 100% !important; }
                            td.full-width-mobile { width: auto !important; }
                        }
                    </style>
                    <style type="text/css">.hide_on_mobile { display: none !important;} 
                        @media only screen and (min-width: 480px) { .hide_on_mobile { display: block !important;} }
                        .hide_section_on_mobile { display: none !important;} 
                        @media only screen and (min-width: 480px) { 
                            .hide_section_on_mobile { 
                                display: table !important;
                            } 
    
                            div.hide_section_on_mobile { 
                                display: block !important;
                            }
                        }
                        .hide_on_desktop { display: block !important;} 
                        @media only screen and (min-width: 480px) { .hide_on_desktop { display: none !important;} }
                        .hide_section_on_desktop { 
                            display: table !important;
                            width: 100%;
                        } 
                        @media only screen and (min-width: 480px) { .hide_section_on_desktop { display: none !important;} }
                        
                        p, h1, h2, h3 {
                            margin: 0px;
                        }
    
                        ul, li, ol {
                            font-size: 11px;
                            font-family: Ubuntu, Helvetica, Arial;
                        }
    
                        a {
                            text-decoration: none;
                            color: inherit;
                        }
    
                        @media only screen and (max-width:480px) {
                            .mj-column-per-100 { width:100%!important; max-width:100%!important; }
                            .mj-column-per-100 > .mj-column-per-75 { width:75%!important; max-width:75%!important; }
                            .mj-column-per-100 > .mj-column-per-60 { width:60%!important; max-width:60%!important; }
                            .mj-column-per-100 > .mj-column-per-50 { width:50%!important; max-width:50%!important; }
                            .mj-column-per-100 > .mj-column-per-40 { width:40%!important; max-width:40%!important; }
                            .mj-column-per-100 > .mj-column-per-33 { width:33.333333%!important; max-width:33.333333%!important; }
                            .mj-column-per-100 > .mj-column-per-25 { width:25%!important; max-width:25%!important; }
                            .mj-column-per-100 { width:100%!important; max-width:100%!important; }
                            .mj-column-per-75 { width:100%!important; max-width:100%!important; }
                            .mj-column-per-60 { width:100%!important; max-width:100%!important; }
                            .mj-column-per-50 { width:100%!important; max-width:100%!important; }
                            .mj-column-per-40 { width:100%!important; max-width:100%!important; }
                            .mj-column-per-33 { width:100%!important; max-width:100%!important; }
                            .mj-column-per-25 { width:100%!important; max-width:100%!important; }
                        }
                    </style>
                        
                    </head>
                    <body style="background-color:#FFFFFF;">
                        <div style="background-color:#FFFFFF;">
                            <!--[if mso | IE]>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600">
                                <tr>
                                    <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                                        <![endif]-->
                                        <div style="margin:0px auto;max-width:600px;">
                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                                <tbody>
                                                    <tr>
                                                        <td style="direction:ltr;font-size:0px;padding:9px 0px 9px 0px;text-align:center;">
                                                            <!--[if mso | IE]>
                                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                                <tr>
                                                                    <td class="" style="vertical-align:top;width:600px;">
                                                            <![endif]-->
                                                                        <div class="mj-column-per-100 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                                <tr>
                                                                                    <td align="center" style="font-size:0px;padding:28px 0px 0px 0px;word-break:break-word;">
                                                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                                                            <tbody>
                                                                                            <tr>
                                                                                                <td style="width:132px;">
                                                                                                    <img alt="Pojok berbagi" height="auto" src="https://s3-eu-west-1.amazonaws.com/topolio/uploads/61da813af1cbc/1641709984.jpg" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="132">
                                                                                                </td>
                                                                                            </tr>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                            <!--[if mso | IE]>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <![endif]-->
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <!--[if mso | IE]>
                                    </td>
                                </tr>
                            </table>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600">
                                <tr>
                                    <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                        <div style="margin:0px auto;max-width:600px;">   
                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                                <tbody>
                                                    <tr>
                                                        <td style="direction:ltr;font-size:0px;padding:0px 0px 0px 0px;text-align:center;">
                                                            <!--[if mso | IE]>
                                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                                <tr>
                                                                    <td class="" style="vertical-align:top;width:600px;">
                                                                    <![endif]-->
                                                                        <div class="mj-column-per-100 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">    
                                                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                                <tr>
                                                                                    <td align="left" style="font-size:0px;padding:15px 15px 15px 15px;word-break:break-word;">
                                                                                        <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial; text-align: center;"><span style="font-size: 16px;"><strong>Assalamualaikum, '. strip_tags($params["nama_donatur"]) .'</strong></span></p></div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="left" style="font-size:0px;padding:15px 15px 15px 15px;word-break:break-word;">
<<<<<<< HEAD
                                                                                        <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial;">Alhamdullilah <strong>'. strip_tags($params["penerima_bantuan"]) .'</strong> akan menapatkan bantuan dari anda, berikut adalah rincian sekilas pembayaran yang dapat anda lakukan melalui <strong>'. strip_tags($params["metode_bayar"]) .'</strong> <strong>'. strip_tags($params["nama_cp"]) .'</strong>.&nbsp;</p></div>
=======
                                                                                        <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial;">Alhamdullilah <strong>'. strip_tags($params["penerima_bantuan"]) .'</strong> akan menapatkan bantuan dari anda, berikut adalah rincian sekilas pembayaran yang dapat anda lakukan melalui <strong>'. strip_tags($params["metode_bayar"]) .'</strong> <strong>'. strip_tags($params["nama_partner"]) .'</strong>.&nbsp;</p></div>
>>>>>>> f611ab7aefc8a1db8f9fd2871435bab3676fbee5
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="left" style="font-size:0px;padding:15px 15px 15px 15px;word-break:break-word;">
                                                                                        <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial; text-align: center;"><span style="font-size: 14px;"><strong>Kirim Ke</strong></span></p></div>
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                        </div>
                                                                    <!--[if mso | IE]>
                                                                    </td>                    
                                                                </tr>
                                                            </table>
                                                            <![endif]-->
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <!--[if mso | IE]>
                                    </td>
                                </tr>
                            </table>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600">
                                <tr>
                                    <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                        <div style="margin:0px auto;max-width:600px;">
                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                            <tbody>
                                                <tr>
                                                    <td style="direction:ltr;font-size:0px;padding:9px 0px 9px 0px;text-align:center;">
                                                    <!--[if mso | IE]>
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="" style="vertical-align:top;width:300px;">
                                                                <![endif]-->
                                                                    <div class="mj-column-per-50 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:50%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                            <tr>
                                                                                <td align="left" style="font-size:0px;padding:0px 15px 0px 15px;word-break:break-word;">
<<<<<<< HEAD
                                                                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p class="partners" style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial;"><img style="display: block; margin-left: auto; margin-right: auto;" src="https://pojokberbagi.id'. strip_tags($params["partner_image_url"]) .'" alt="'. strip_tags($params["nama_cp"]) .'" width="69" height="39"></p></div>
=======
                                                                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p class="partners" style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial;"><img style="display: block; margin-left: auto; margin-right: auto;" src="https://pojokberbagi.id'. strip_tags($params["partner_image_url"]) .'" alt="'. strip_tags($params["nama_partner"]) .'" width="69" height="39"></p></div>
>>>>>>> f611ab7aefc8a1db8f9fd2871435bab3676fbee5
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                <!--[if mso | IE]>
                                                                </td>
                                                                <td class="" style="vertical-align:top;width:300px;">
                                                                <![endif]-->
                                                                    <div class="mj-column-per-50 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:50%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                            <tr>
                                                                                <td align="left" style="font-size:0px;padding:0px 15px 0px 15px;word-break:break-word;">
                                                                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><div class="account">
                                                                                        <p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial; text-align: center;"><span style="font-size: 14px;"><strong>'. strip_tags($params["nomor_tujuan_bayar"]) .'</strong></span></p>
                                                                                        <p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial; text-align: center;">A/N&nbsp;'. strip_tags($params["atas_nama_tujuan_bayar"]) .'</p>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                            </table>
                                        </div>
                                    <!--[if mso | IE]>
                                    </td>
                                </tr>
                            </table>               
                            <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600">
                                <tr>
                                    <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                                    <![endif]-->                       
                                    <div style="margin:0px auto;max-width:600px;">
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                        <tbody>
                                            <tr>
                                                <td style="direction:ltr;font-size:0px;padding:0px 0px 0px 0px;text-align:center;">
                                                <!--[if mso | IE]>
                                                    <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:top;width:600px;"
                                                            <![endif]-->
                                                                <div class="mj-column-per-100 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                        <tr>
                                                                            <td align="left" style="font-size:0px;padding:15px 15px 15px 15px;word-break:break-word;">
                                                                                <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial; text-align: center;"><span style="font-size: 14px;">Nominal Transfer&nbsp;<strong>Rp. '. strip_tags($params["jumlah_donasi"]) .'</strong></span></p></div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="font-size:0px;padding:10px 0px;padding-top:10px;padding-right:0px;padding-bottom:10px;padding-left:0px;word-break:break-word;">
                                                                                <p style="font-family: Ubuntu, Helvetica, Arial; border-top: solid 1px #000000; font-size: 1; margin: 0px auto; width: 100%;"></p>
                                                                                <!--[if mso | IE]>
                                                                                <table align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 1px #000000;font-size:1;margin:0px auto;width:600px;" role="presentation" width="600px">
                                                                                    <tr>
                                                                                        <td style="height:0;line-height:0;">&nbsp;</td>
                                                                                    </tr>
                                                                                </table>
                                                                                <![endif]-->
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            <!--[if mso | IE]>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                <![endif]-->
                                                </td>
                                            </tr>
                                        </tbody>
                                        </table>
                                    </div>
                                    <!--[if mso | IE]>
                                    </td>
                                </tr>
                            </table>             
                            <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600">
                                <tr>
                                    <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                        <div style="margin:0px auto;max-width:600px;">
                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                            <tbody>
                                                <tr>
                                                    <td style="direction:ltr;font-size:0px;padding:0px 0px 0px 0px;text-align:center;">
                                                    <!--[if mso | IE]>
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="" style="vertical-align:top;width:600px;">
                                                                <![endif]-->
                                                                    <div class="mj-column-per-100 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                            <tr>
                                                                                <td align="center" style="font-size:0px;padding:0px 0px 0px 0px;word-break:break-word;">    
                                                                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td style="width:600px;">
                                                                                                <img alt="Donasi Notif" height="auto" src="https://s3-eu-west-1.amazonaws.com/topolio/uploads/61da813af1cbc/1641710417.jpg" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="600">
                                                                                            </td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                    </table>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                            </table>
                                        </div>
                                    <!--[if mso | IE]>
                                    </td>
                                </tr>
                            </table>       
                            <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600">
                                <tr>
                                    <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                        <div style="margin:0px auto;max-width:600px;">
                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                            <tbody>
                                                <tr>
                                                    <td style="direction:ltr;font-size:0px;padding:9px 0px 9px 0px;text-align:center;">
                                                    <!--[if mso | IE]>
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">    
                                                            <tr>
                                                                <td style="vertical-align:top;width:600px;">
                                                                <![endif]-->
                                                                    <div class="mj-column-per-100 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                            <tr>
                                                                                <td align="left" style="font-size:0px;padding:15px 15px 15px 15px;word-break:break-word;">
                                                                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial; text-align: left;"><span style="font-size: 14px;"><strong>Wahai '. strip_tags($params["samaran"]) .', terima kasih sudah mempercayakan dana donasi untuk '. strip_tags($params["nama_bantuan"]) .' anda kepada kami.&nbsp;</strong>Semoga doa, harapan anda dan '. strip_tags($params["penerima_bantuan"]) .' dapat terwujud dalam bentuk yang terbaik.</span></p></div>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                            </table>
                                        </div>
                                    <!--[if mso | IE]>
                                    </td>
                                </tr>
                            </table>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600">
                                <tr>
                                    <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                        <div style="margin:0px auto;max-width:600px;">
                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                            <tbody>
                                                <tr>
                                                    <td style="direction:ltr;font-size:0px;padding:9px 0px 9px 0px;text-align:center;">
                                                    <!--[if mso | IE]>
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td style="vertical-align:top;width:300px;">
                                                                <![endif]-->
                                                                    <div class="mj-column-per-50 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:50%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                            <tr>
                                                                                <td align="left" style="font-size:0px;padding:15px 15px 15px 15px;word-break:break-word;">
                                                                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial;"><span style="font-size: 14px;"><strong>Jangan Lupa Follow Medsos Kami Di</strong></span></p></div>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                <!--[if mso | IE]>
                                                                </td>
                                                                <td style="vertical-align:top;width:300px;">
                                                                <![endif]-->
                                                                    <div class="mj-column-per-50 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:50%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                            <tr>
                                                                                <td align="center" style="font-size:0px;padding:0px 10px 0px 10px;word-break:break-word;">
                                                                                    <!--[if mso | IE]>
                                                                                    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation">
                                                                                        <tr>
                                                                                            <td>
                                                                                            <![endif]-->
                                                                                                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;">
                                                                                                    <tr>
                                                                                                        <td style="padding:4px;">
                                                                                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:transparent;border-radius:3px;width:40px;">
                                                                                                                <tr>
                                                                                                                    <td style="font-size:0;height:40px;vertical-align:middle;width:40px;">
                                                                                                                        <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.facebook.com/pojokberbagi" target="_blank" style="color: #0000EE;">
                                                                                                                            <img alt="facebook" height="40" src="https://s3-eu-west-1.amazonaws.com/ecomail-assets/editor/social-icos/outlined/facebook.png" style="border-radius:3px;display:block;" width="40">
                                                                                                                        </a>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                </table>
                                                                                            <!--[if mso | IE]>
                                                                                            </td>
                                                                                            <td>
                                                                                                <![endif]-->
                                                                                                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;">
                                                                                                    <tr>
                                                                                                        <td style="padding:4px;">
                                                                                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:transparent;border-radius:3px;width:40px;">
                                                                                                                <tr>
                                                                                                                <td style="font-size:0;height:40px;vertical-align:middle;width:40px;">
                                                                                                                    <a href="https://twitter.com/home?status=https://www.twitter.com/pojok_berbagi" target="_blank" style="color: #0000EE;">
                                                                                                                        <img alt="twitter" height="40" src="https://s3-eu-west-1.amazonaws.com/ecomail-assets/editor/social-icos/outlined/twitter.png" style="border-radius:3px;display:block;" width="40">
                                                                                                                    </a>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                </table>
                                                                                                <!--[if mso | IE]>
                                                                                            </td>
                                                                                            <td>
                                                                                            <![endif]-->
                                                                                                <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;">       
                                                                                                    <tr>
                                                                                                        <td style="padding:4px;">
                                                                                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:transparent;border-radius:3px;width:40px;">
                                                                                                                <tr>
                                                                                                                    <td style="font-size:0;height:40px;vertical-align:middle;width:40px;">
                                                                                                                        <a href="https://www.instagram.com/pojokberbagi.id" target="_blank" style="color: #0000EE;">
                                                                                                                            <img alt="instagram" height="40" src="https://s3-eu-west-1.amazonaws.com/ecomail-assets/editor/social-icos/outlined/instagram.png" style="border-radius:3px;display:block;" width="40">
                                                                                                                        </a>
                                                                                                                    </td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                </table>
                                                                                            <!--[if mso | IE]>
                                                                                            </td>
                                                                                        </tr>
                                                                                    </table>
                                                                                <![endif]-->
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                            </table>
                                        </div>
                                    <!--[if mso | IE]>
                                    </td>
                                </tr>
                            </table>
                            <table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600">
                                <tr>
                                    <td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                        <div style="margin:0px auto;max-width:600px;">
                                            <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                            <tbody>
                                                <tr>
                                                    <td style="direction:ltr;font-size:0px;padding:9px 0px 9px 0px;text-align:center;">
                                                    <!--[if mso | IE]>
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td style="vertical-align:top;width:600px;">
                                                                <![endif]-->
                                                                    <div class="mj-column-per-100 outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                                                            <tr>
                                                                                <td align="left" style="font-size:0px;padding:15px 15px 15px 15px;word-break:break-word;">
                                                                                    <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;">
                                                                                        <p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial;"><strong>Sekertariat Lembaga</strong></p>
                                                                                        <p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial;">Graha Berbagi Jl. Kuningan Raya No 86 Kel. Antapani Kidul, Kec. Antapani, Kota Bandung Prov. Jawa Barat.&nbsp;</p>
                                                                                        <p style="font-size: 11px; font-family: Ubuntu, Helvetica, Arial;">Kontak WA <span style="color: #169179;"><strong>0812-1333-3111.</strong></span></p>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </div>
                                                                <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                            </table>
                                        </div>
                                    <!--[if mso | IE]>
                                    </td>
                                </tr>
                            </table>
                            <![endif]-->
                        </div>
                    </body>
                    </html>';
        }
        return false;
    }

    public static function emailFollowUpDonasi($data = array()) {
        if (isset($data)) {
            return '<!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Notif Donasi CR</title>
                    <style>
                        @import url("https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap");
                        * {
                            font-family: "Nunito";
                            font-size: 14px;
                        }
                        table {
                            width: 100%;
                            max-width: 600px;
                            margin: auto;
                        }
                        #logo {
                            width: 96px;
                            height: auto;
                        }
                        .rounded-box {
                            border-radius: 20px;
                        }
                        #head-box,
                        #body-box,
                        #foot-box {
                            padding: 1em;
                            margin-right: 1em;
                            margin-left: 1em;
                        }
                        #head-box {
                            background-color: white;
                            position: relative;
                        }
                        #head-box::before,
                        #head-box::after {
                            position: absolute;
                            content: "";
                            height: 40px;
                            width: 40px;
                            z-index: -1;
                        }
                        #head-box::before {
                            top: -.5em;
                            right: -.5em;
                            background-color: #FE5000;
                        }
                        #head-box::after {
                            bottom: -.5em;
                            left: -.5em;
                            background-color: #97D700;
                        }
                        #body-box {
                            box-shadow: 0px 0px 10px 0px whitesmoke;
                            background-color: whitesmoke;
                        }
                        .pt-1 {
                            padding-top: 1em;
                        }
                        .mt-1 {
                            margin-top: 1em;
                        }
                        .mt-2 {
                            margin-top: 2em;
                        }
                        @media screen and (max-width: 480px) {
                            #nominal strong {
                                font-size: 1.25rem !important;
                            }

                            #body-box,
                            #foot-box {
                                margin-left: 0px;
                                margin-right: 0px;
                            }

                            #foot-box {
                                padding-left: 0px;
                                padding-right: 0px;
                            }
                            #copy small {
                                padding-left: 0px !important;
                                padding-right: 0px !important;
                                margin: 0px !important;
                            }
                        }
                    </style>
                </head>
                <body>
                    <table>
                        <tr>
                            <td>
                                <center><a href="https://pojokberbagi.id"><img src="https://pojokberbagi.id/assets/images/brand/pojok-berbagi-transparent.png" alt="Pojok Berbagi" id="logo"></a></center>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div id="head-box" class="mt-1 rounded-box">
                                    <h3>Assalamualaikum, '. strip_tags($data["nama_karyawan"]) .'</h3></>
                                    <p>Donatur baru tengah melakukan donasi, mohon untuk difollow-up prihal proses transaksinya.</p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div id="body-box" class="rounded-box mt-2">
                                    <table>
                                        <tr style="vertical-align: text-top;">
                                            <td style="width: 50%;">
                                                <h4 style="margin: 0px; opacity: .5; margin-bottom: 1rem;">Data Donatur</h4>
                                                <b style="margin: .25rem 0px; opacity: .5; display: block;">Nama</b>
                                                <b style="margin: 0rem 0px; display: block;">'. strip_tags($data["nama_donatur"]) .'</b>
                                                <b style="margin: .25rem 0px; opacity: .5; display: block; padding-top: .25rem;">Kontak</b>
                                                <b style="margin: 0rem 0px; display: block;">'. strip_tags($data["kontak_donatur"]) .'</b>
                                                <b style="margin: .25rem 0px; opacity: .5; display: block; padding-top: .25rem;">Email</b>
                                                <b style="margin: 0rem 0px; display: block;">'. strip_tags($data["email_donatur"]) .'</b>
                                            </td>
                                            <td style="width: 50%;">
                                                <h4 style="margin: 0px; opacity: .5; margin-bottom: 1rem;">'. strip_tags($data["nama_bantuan"]) .'</h4>
                                                <b style="margin: .25rem 0px; display: block;">'. strip_tags($data["penerima_donasi"]) .'</b>
                                                '. (isset($data["doa_dan_pesan"]) ? '<p style="margin: .25rem 0px; opacity: .75; display: block;">'. strip_tags($data["doa_dan_pesan"]) .' </p>' : 'Tidak ada pesan dari donatur' ) .'
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div id="foot-box">
                                    <table>
                                        <tr id="nominal">
                                            <td><strong style="font-size: 2rem;">Nominal</strong></td>
                                            <td style="text-align: right;" colspan="2"><strong style="color:  #FE5000; font-size: 2rem;">Rp. '. strip_tags($data["jumlah_donasi"]) .'</strong></></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p style="margin-bottom: 0px;">NOMOR DONASI</p>
                                                <strong>NDPBI-'. strip_tags($data["id_donasi"]) .'</strong>
                                            </td>
                                            <td id="transfer">
                                                <p style="margin-bottom: 0px;">Transfer</p>
<<<<<<< HEAD
                                                <strong>'. strip_tags($data["nama_cp"]) .'</strong>
=======
                                                <strong>'. strip_tags($data["nama_partner"]) .'</strong>
>>>>>>> f611ab7aefc8a1db8f9fd2871435bab3676fbee5
                                            </td>
                                            <td>
                                                <img src="https://pojokberbagi.id/assets/images/brand/patern-horizontal.png" alt="Patern Horizontal" style="max-width: 80px; width: 100%; max-height: calc(80px/16*9); height: auto; float:right;">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <tr id="copy">
                            <td>
                                <small style="margin: 1em; padding: 0px 1em; opacity: .75;">&copy; 2022. All Trade Right Reserve Pojok Berbagi.</small>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>';
        }
        return false;
    }
<<<<<<< HEAD

    public static function emailResetPassword($data = array()) {
        if (isset($data)) {
            return '<!DOCTYPE html>
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Ubah Password</title>
                <style>
                    @import url("https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap");
                    * {
                        font-family: "Nunito", sans-serif;
                    }
                    a:hover {
                        opacity: .9;
                    }
                </style>
            </head>
            <body>
                <table style="max-width: 500px; margin: auto;">
                    <tr>
                        <td style="text-align: center;">
                            <img src="https://pojokberbagi.id/assets/images/brand/pojok-berbagi-transparent.png" alt="Pojok berbagi" style="max-width: 100px; margin-bottom: 1em;">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="padding: 1.5em; border-radius: 20px; background-color: aliceblue;">
                                <h3>
                                    Hi '. strip_tags($data["nama"]) .',
                                </h3>
                                <p>
                                    Kami menerima permintaan untuk mengubah password!
                                </p>
                                <p>
                                    Jika anda merasa tidak melakukan permintaan tersebut, lupakan email ini.
                                </p>
                                <p>
                                    Jika sebaliknya, silahkan klik tombol berikut untuk mengganti password anda: 
                                </p>
                                <table style="width: 100%;">
                                    <tr>
                                        <td>
                                            <a href="'. strip_tags($data["link"]) .'" style="padding: 0.75em; border-radius: 10px; background-color: #FE5000; color: white; text-decoration: none;">
                                                Reset Password
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <p style="font-size: 14px;">
                                                Kadaluarsa pada: '. strip_tags($data["expiry"]) .'
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 1.5em; text-align: center">
                            <small>
                                <span>
                                    Jl. Kuningan Raya No. 86, Kel. Antapani Kidul, Kec. Antapani, Kota Bandung, Provinsi Jawa Barat.
                                </span>
                                <div style="color: lightslategrey; display: flex; gap: 0.5rem; justify-content: center;">
                                    <span>Telepon 022 21210292 - WA 0821 1113 3331</span>
                                </div>
                            </small>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';
        }
    }

    public static function emailHookAkun($data = array()) {
        if (isset($data)) {
            return '<!DOCTYPE html>
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Kaitkan Akun</title>
                <style>
                    @import url("https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap");
                    * {
                        font-family: "Nunito", sans-serif;
                    }
                    a:hover {
                        opacity: .9;
                    }
                </style>
            </head>
            <body>
                <table style="max-width: 500px; margin: auto;">
                    <tr>
                        <td style="text-align: center;">
                            <img src="https://pojokberbagi.id/assets/images/brand/pojok-berbagi-transparent.png" alt="Pojok berbagi" style="max-width: 100px; margin-bottom: 1em;">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="padding: 1.5em; border-radius: 20px; background-color: aliceblue;">
                                <h3>
                                    Hi '. strip_tags($data["nama"]) .',
                                </h3>
                                <p>
                                    Kami menerima permintaan untuk mengkaitkan akun anda!
                                </p>
                                <p>
                                    Jika anda merasa tidak melakukan permintaan tersebut, lupakan email ini.
                                </p>
                                <p>
                                    Jika sebaliknya, silahkan klik tombol <strong>Kaitkan Akun</strong> untuk kaitkan akun dengan donasi anda.
                                </p>
                                <table style="width: 100%;">
                                    <tr>
                                        <td>
                                            <center>
                                                <a href="'. strip_tags($data["link"]) .'" style="padding: 0.75em; border-radius: 10px; background-color: #FE5000; color: white; text-decoration: none; margin: auto;">
                                                    Kaitkan Akun
                                                </a>
                                            </center>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 1.5em; text-align: center">
                            <small>
                                <span>
                                    Jl. Kuningan Raya No. 86, Kel. Antapani Kidul, Kec. Antapani, Kota Bandung, Provinsi Jawa Barat.
                                </span>
                                <div style="color: lightslategrey; display: flex; gap: 0.5rem; justify-content: center;">
                                    <span>Telepon 022 21210292 - WA 0821 1113 3331</span>
                                </div>
                            </small>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';
        }
    }
=======
>>>>>>> f611ab7aefc8a1db8f9fd2871435bab3676fbee5
}