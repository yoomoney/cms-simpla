
### Создание платежа

Для того, чтобы создать платёж необходимо вызвать метод клиента createPayment, который принимает в качестве аргумента
объект типа [CreatePaymentRequestInterface](../lib/Request/Payments/CreatePaymentRequestInterface.php). Для генерации
объектов используется билдер запросов [CreatePaymentRequestBuilder](../lib/Request/Payments/CreatePaymentRequestBuilder.php) инстанс
которого можно получить вызвав метод [CreatePaymentRequest::builder()](../lib/Request/Payments/CreatePaymentRequest.php#builder).

Процесс создания запроса выглядит примерно так:
```php
use YaMoney\Commmon\Exceptions\InvalidPropertyException;
use YaMoney\Commmon\Exceptions\InvalidRequestException;
use YaMoney\Model\CurrencyCode;
use YaMoney\Request\Payments\CreatePaymentRequest;

// ...

$builder = CreatePaymentRequest::builder();
try {
    $builder->setAmount(200.01)
        ->setCurrency(CurrencyCode::USD);
    // ... устанавливаем другие свойства платежа
    
    $request = $builder->build();
} catch (InvalidPropertyException $e) {
    // при установке одного из свойств запроса произошла ошибка

    // ... как-то обрабатываем ошибку
} catch (InvalidRequestException $e) {
    // при валидации запроса произошла ошибка, возможно не задано одно из обязательных
    // свойств запроса или одновременно установлено несколько способов проведения платежа

    // ... как-то обрабатываем ошибку
}
```
После того как объект запроса к API собран, можно отправить запрос и попытаться провести платёж:
```php
try {
    // При создании платежа idempotenceKey обязателен
    $idempotenceKey = $orderId . '-' . $clientId . '-' . microtime(true);
    $response = $client->createPayment($request, $idempotenceKey);
    $triesCount = 1;
    while ($response === null) {
        // если от API вернулся ответ 201, то ошибка не возникает, а в качестве ответа приходин null
        // пробуем получить платеж через 5 секунд
        sleep(5);
        $response = $client->createPayment($request, $idempotenceKey);
        $triesCount++;
        if ($triesCount > $maxCount) {
            // достигли лимита попыток проведения платежа
            // ...
            break;
        }
    }
    if ($response->hasError()) {
        // платёж попал в кассу, но что-то внутри пошло не так
        // ...
    } elseif ($response->getPaid()) {
        // платёж прошёл и проведён
        // ...
    } else {
        // платёж в кассе, но пока не проведён
        // ...
    }
} catch (\Exception $e) {
    // что-то пошло не так и провести платёж не удалось
    
    // ... как-то обрабатываем ошибку
}
```
При создании платежа необходимо отправить способ проведения платежа, должено быть установлено одно из свойств с помощью методов:
* setPaymentToken() устанавливает токен платежа;
* setPaymentMethodId() устанавливает идентификатор способа оплаты, сохранённый до этого;
* setPaymentMethodData() устанавливает данные используемые для создания метода оплаты.

К примеру чтобы провести платёж с помощью банковской карты на стороне кассы, нужно установить параметры примерно так:
```php
$builder->setPaymentMethodData(PaymentMethodType::BANK_CARD);
```

Для получения ссылки на страницу ввода информации о платеже нужно указать стовоб подтверждения "redirect":
```php
$builder->setConfirmation(array(
    'type' => ConfirmationType::REDIRECT,
    'return_url' => 'https://example.com/process-payment-result',
));
```
Тогда в полученном от API ответе с информацией о платеже в поле "confirmation.confirmation_url" придёт ссылка на страницу для воода информации:
```php
$url = $response->getConfirmation()->getConfirmationUrl();
header('Location: ' . $url);
```

### Список методов билдера запроса на создание платежа
* setShopId - устанавливает идентификатор магазина получателя платежа;
* setProductGroupId - устанавливает идентификатор товара;
* setAmount - устанавливает сумму заказа;
* setCurrency - устанавливает валюту в которой заказ оплачивается;
* setReceiptItems - устанавливает список товаров в заказе для создания чека;
* addReceiptItem - добавляет в чек товар;
* addReceiptShipping - добавляет в чек доставку товара;
* setReceiptEmail - устанавливает адрес электронной почты получателя чека;
* setReceiptPhone - устанавливает телефон получателя чека;
* setTaxSystemCode - устанавливает код системы налогообложения;
* setPaymentToken - устанавливает одноразовый токен для проведения оплаты;
* setPaymentMethodId - устанавливает идентификатор записи о сохранённых данных покупателя;
* setPaymentMethodData - устанавливает объект с информацией для создания метода оплаты;
* setConfirmation - устанавливает способ подтверждения платежа;
* setSavePaymentMethod - устанавливает флаг сохранения платёжных данных. Значение true инициирует создание многоразового payment_method;
* setCapture - устанавливает флаг автоматического принятия поступившей оплаты;
* setClientIp - устанавливает IP адрес покупателя;
* setMetadata - устанавливает метаданные, привязанные к платежу.
