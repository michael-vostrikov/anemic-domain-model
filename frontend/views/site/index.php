<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'My Yii Application';

$endpointDescriptions = [
    ['method' => 'GET', 'url' => '/product/view', 'get-params' => ['id' => 1]],
    ['method' => 'POST', 'url' => '/product/create', 'data' => [
        'name' => 'Product 4',
    ]],
    ['method' => 'POST', 'url' => '/product/save', 'get-params' => ['id' => 1], 'data' => [
        'category_id' => 2,
        'name' => 'Product 1',
        'description' => 'Description 1',
    ]],
    ['method' => 'POST', 'url' => '/product/send-for-review', 'get-params' => ['id' => 1]],
    ['method' => 'POST', 'url' => 'http://127.0.0.1:81/review/accept', 'get-params' => ['id' => 1], 'data' => null],
    ['method' => 'POST', 'url' => 'http://127.0.0.1:81/review/decline', 'get-params' => ['id' => 1], 'data' => null],
];
?>
<style>
    .endpoint-description td {
        padding: 4px;
        vertical-align: top;
        font-size: 14px;
    }
    .endpoint-description .get-params {
        display: inline-block;
        padding: 0;
        border: 0;
    }
    .endpoint-description .data, .endpoint-description .response {
        font-family: var(--bs-font-monospace);
        width: 30rem;
        height: 13rem;
    }
</style>
<div class="site-index">
    <?php foreach ($endpointDescriptions as $description) { ?>
        <div class="endpoint-description">
            <table>
                <tr>
                    <td>
                        <?php
                            echo Html::tag('span', $description['method'], ['class' => 'method']);
                            echo ' ';
                            echo Html::tag('span', $description['url'], ['class' => 'url']);
                            if ($description['get-params'] ?? false) {
                                echo '?';
                                echo Html::input('text', null, http_build_query($description['get-params']), ['class' => 'get-params']);
                            }
                            echo '<br>';

                            $value = isset($description['data'])
                                ? json_encode($description['data'], JSON_PRETTY_PRINT)
                                : '';
                            echo Html::textarea('', $value, ['class' => 'data']);
                        ?>
                    </td>
                    <td>
                        <br>
                        <button class="send">Send</button>
                    </td>
                    <td>
                        <span class="code"></span>
                        <br>
                        <textarea class="response"></textarea>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
</div>

<script>
    <?php ob_start(); ?>
    (function () {
      $('.endpoint-description .send').on('click', async function () {
        const parent = $(this).closest('.endpoint-description');

        const method = $('.method', parent).text();
        const url = $('.url', parent).text();
        const getParams = $('.get-params', parent).val();
        const data = $('.data', parent).val();

        const params = {
          method,
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': $('[name="csrf-token"]').attr('content'),
            'Authorization': 'Bearer api_token',
          },
        };
        if (method !== 'GET' && method !== 'DELETE') {
          params.body = data;
        }

        $('.code', parent).html('');
        $('.response', parent).val('');

        await fetch(url + (getParams ? '?' + getParams : ''), params)
          .then(res => {
            $('.code', parent).html(res.status + ' ' + res.statusText);
            return res.text();
          })
          .then(text => {
            let val;
            try {
              const json = JSON.parse(text);
              val = JSON.stringify(json, null, 4);
            } catch (error) {
              val = text;
            }

            $('.response', parent).val(val);
          });
      });
    })();
    <?php $this->registerJs(ob_get_clean()); ?>
</script>
