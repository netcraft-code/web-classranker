<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to PayU...</title>
</head>
<body onload="document.forms[0].submit()">

<form method="POST" action="{{ $data['payu_url'] }}">
    @foreach($data as $key => $value)
        @if($key !== 'payu_url')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
</form>

<p>Redirecting to payment gateway...</p>

</body>
</html>