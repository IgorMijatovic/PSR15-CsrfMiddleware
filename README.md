# PSR-15 Middleware
<<<<<<< HEAD

[![Build Status](https://travis-ci.org/IgorMijatovic/PSR15-CsrfMiddleware.svg?branch=master)](https://travis-ci.org/IgorMijatovic/PSR15-CsrfMiddleware)
[![Coverage Status](https://coveralls.io/repos/github/IgorMijatovic/PSR15-CsrfMiddleware/badge.svg?branch=master)](https://coveralls.io/github/IgorMijatovic/PSR15-CsrfMiddleware?branch=master)

  This middleware checj every POST, PUT or DELETE request for a CSRF token.
  Token are persisted using an ArrayAccess compatible Session and are generated on demand.
  
  $middleware = new CsrfMiddleware($_SESSION, 200);
  $app-pipe($middleware);
  
  
  //Generate iput
  
  $input = "<input type="hidden" name="{$middleware->getFormKey(})}" value="{$middleware->generateToken(})}"/>