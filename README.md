# PSR-15 Middleware
  This middleware checj every POST, PUT or DELETE request for a CSRF token.
  Token are persisted using an ArrayAccess compatible Session and are generated on demand.
  
  $middleware = new CsrfMiddleware($_SESSION, 200);
  $app-pipe($middleware);
  
  
  //Generate iput
  
  $input = "<input type="hidden" name="{$middleware->getFormKey(})}" value="{$middleware->generateToken(})}"/>