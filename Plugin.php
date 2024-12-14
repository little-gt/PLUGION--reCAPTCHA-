<?php
/**
 * 插件名称: 留言reCAPTCHA验证
 * 描述: 在评论提交时验证reCAPTCHA.
 * 版本: 1.0
 * 作者: @little-gt
 */

class Widget_Comments_Recaptcha extends Typecho_Widget implements Typecho_Widget_Interface_Do
{
    public function action()
    {
        if ($this->request->isPost()) {
            $siteKey = $this->options->theme->recaptchaSiteKey;
            $secretKey = $this->options->theme->recaptchaSecretKey;

            if (!empty($siteKey) && !empty($secretKey)) {
                if (isset($_POST['g-recaptcha-response'])) {
                    $response = $_POST['g-recaptcha-response'];
                    $verifyUrl = "https://www.recaptcha.net/recaptcha/api/siteverify";
                    $data = [
                        'secret'   => $secretKey,
                        'response' => $response
                    ];
                    $options = [
                        'http' => [
                            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method'  => 'POST',
                            'content' => http_build_query($data)
                        ]
                    ];
                    $context  = stream_context_create($options);
                    $result = file_get_contents($verifyUrl, false, $context);
                    $success = json_decode($result)->success;

                    if (!$success) {
                        $this->widget('Widget_Notice')->set(_t('reCAPTCHA验证失败'), 'error');
                        $this->response->goBack();
                    }
                } else {
                    $this->widget('Widget_Notice')->set(_t('缺少reCAPTCHA响应'), 'error');
                    $this->response->goBack();
                }
            }
        }

        // 继续执行默认的评论处理逻辑
        $this->factory->comment->push($this);
    }
}