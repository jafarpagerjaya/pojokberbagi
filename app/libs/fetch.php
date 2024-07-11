<?php
class Fetch {
    private $_result = array('error' => true),
            $_decoded;

    public $path_gambar;

    public function __construct($token = true) {
        if (!isset($_SERVER['HTTP_REFERER']) && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Redirect::to('/');
        }

        // Check Content Type and decode JSON to array
        $this->_decoded = $this->contentTypeJsonDecoded($_SERVER["CONTENT_TYPE"]);

        // Check Token
        if ($token) {
            if (!$this->checkToken($this->_decoded['token'])) { return false; }
        }
    }

    public function index() {
        $this->_result['feedback'] = array(
            'message' => 'Fetch ke Method index ?, mohon periksa kembali tujuan fetchnya, sudah ada atau belum'
        );
        $this->result();
        return false;
    }

    private function checkToken($token) {
        if (!Token::check($token)) {
            $this->_result['feedback'] = array(
                'message' => 'You not allowed to use fake token'
            );
            $this->result();
            return false;
        }
        return true;
    }

    private function contentTypeJsonDecoded($server_content_type) {
        $contentType = isset($server_content_type) ? trim($server_content_type) : '';

        if ($contentType !== "application/json") {
            $this->_result['feedback'] = array(
                'message' => 'Unrecognize contentType JSON ONLY'
            );
            $this->result();
            return false;
        }

        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        return $decoded;
    }

    public function getDecoded() {
        return $this->_decoded;
    }

    public function addResults($array) {
        $this->_result = array_merge($this->_result, $array);
    }

    public function result() {
        $this->_result[Config::get('session/token_name')] = Token::generate();
        $toast = array(
            'error' => $this->_result['error'],
            'data_toast' => 'feedback',
            'id' => Hash::unique()
        );

        if (isset($this->_result['feedback'])) {
            $toast['feedback'] = $this->_result['feedback'];
        }

        $this->_result['toast'] = $toast;
        if (isset($toast['feedback'])) {
            if (array_key_exists('message', $toast['feedback'])) {
                if (!$this->_result['error']) {
                    Session::put('toast', $toast);
                }
            }
        }
        echo json_encode($this->_result);
    }

    public function uploadDataUrlIntoServer($params = array(), $path_dir = 'bantuan', $file_name = '') {
        if (count(is_countable($params) ? $params : []) == 0) {
            return false;
        }

        $i = 0;

        $arrayPath = array();

        foreach($params as $directory_name => $fileImg) {
            $extension = Sanitize::escape2(explode('/', mime_content_type($fileImg))[1]);

            $fileImg = str_replace('data:image/'. $extension .';base64,', '', $fileImg);
            $fileImg = str_replace(' ', '+', $fileImg);
            $fileData = base64_decode($fileImg);

            $upload_directory = BASEURL . "uploads" . DS . "images" . DS . "{$path_dir}" . DS . $directory_name;

            if (!is_dir($upload_directory)) {
                mkdir($upload_directory, 0777, $rekursif = true);
            }

            $path_gambar = $upload_directory. DS . time() . '-' . $directory_name .''. $file_name . '.jpeg';

            $uploaded = file_put_contents($path_gambar, $fileData);

            if (!$uploaded) {
                break;
            } else {
                $path_gambar = "/uploads/images/{$path_dir}/" . $directory_name . "/" . time() . "-" . $directory_name .''. $file_name . ".jpeg";
                $arrayPath[$directory_name] = array(
                    'name' => time() . '-' . $directory_name .''. $file_name,
                    'path' => $path_gambar
                );
                $i++;
            }
            
        }

        $this->path_gambar = $arrayPath;

        if (count(is_countable($params) ? $params : []) != $i) {
            foreach($this->path_gambar as $key => $path_file) {
                $this->removeFile(ROOT . DS . 'public' . DS . $path_file['path']);
            }
            return false;
        }

        return true;
    }

    public function removeFile($path_gambar = null) {
        if (is_null($path_gambar)) {
            return false;
        }

        if (file_exists($path_gambar)) {
            unlink($path_gambar);
        }
    }

    public function removePathGambar() {
        if (count(is_countable($this->path_gambar) ? $this->path_gambar : []) > 0) {
            foreach($this->path_gambar as $key => $path_file) {
                $this->removeFile(ROOT . DS . 'public' . DS . $path_file['path']);
            }

            $this->model('home');
            $j = 0;
            foreach($this->path_gambar as $key => $path_gambar) {
                $this->model->delete('gambar', array('nama', '=', Sanitize::escape2($path_gambar['name'])));
                if (!$this->model->affected()) {
                    $fetch->addResults(array(
                        'error' => true,
                        'feedback' => array(
                            'massage' => 'Gambar gagal dihapus dari database [$fetch]'
                        )
                    ));
                } else {
                    $j++;
                }
            }

            if ($j == count(is_countable($this->path_gambar) ? $this->path_gambar : [])) {
                $fetch->addResults(array(
                    'feedback' => array(
                        'massage' => 'Gambar berhasil dihapus dari database dan direktory [$fetch]'
                    )
                ));
            }
        }
    }


}