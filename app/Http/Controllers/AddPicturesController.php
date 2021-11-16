<?php

namespace App\Http\Controllers;

class AddPicturesController extends Controller
{
    public function addQuestionPicture(array $message_photo): string
    {
        foreach ($message_photo as $elem) {
            $photo_id = $elem->getFileId();
        }

        $res = $this->curlDownloadPicture('https://api.telegram.org/bot2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o/getFile', ['file_id' => $photo_id]);

        if ($res['ok']) {
            $matches = [];
            preg_match('#\.(.+)$#u', $res['result']['file_path'], $matches);

            $src  = 'https://api.telegram.org/file/bot2073248573:AAF9U1RECKhm_uX0XXsFOUfR3tXXWn7_j8o/'.$res['result']['file_path'];
            $rename = md5(time() . basename($src)) . '.' .$matches[1];

            $link = "questions/$rename";
            copy($src, $link);

            return $rename;
        }
    }

    private function curlDownloadPicture(string $link, array $postData): array
    {
        $curl = curl_init($link);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true);
    }
}