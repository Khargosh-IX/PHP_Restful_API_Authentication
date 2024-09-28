  <!-- JWT Handlet -->
  <!-- For Encription, De-cription and Validation -->

  <?php
  require './vendor/autoload.php';
  use Firebase\JWT\JWT;
  use Firebase\JWT\Key;

  class JwtHandler{
    protected $jwt_secret;
    protected $token;
    protected $issuedAt;
    protected $expire;
    protected $jwt;

    // Default Function
    public function __construct(){
      // Default time zone setting
      date_default_timezone_set("Asia/kolkata");
      $this->issuedAt = time();

      // Token validity (3600 seconds = 1hr)
      $this->expire = $this->issuedAt + 3600;

      // Set your secret for your token or signature
      $this->jwt_secret = "this_is_my_secret";
    }

    // Where the data is reaching, first token is verified, validated and authenticated then the data is usable. If authentication is failed data will be destroyed and it will be returned
    public function jwtEncodeData($iss, $data){
      $this->token = array(
        // Adding the identifying to the token (who issue the token)
        "iss" => $iss,
        "aud" => $iss,
        // Adding the current timestamp to the token, for identifing thatwhen the token was issued
        "iat" => $this->issuedAt,
        // Token expiration
        "exp" => $this->expire,
        // payload
        "data" => $data
      );
      $this->jwt = JWT::encode($this->token, $this->jwt_secret, 'HS256');
      return $this->jwt;
    }

    // Issueed token will be validated
    public function jwtDecodedata($jwt_token){
      try {
        $decode = JWT::decode($jwt_token, new Key($this->jwt_secret, 'HS256'));
        return ["data" => $decode->data];
      } catch (Exception $e) {
        return ["message" => $e->getMessage()];
      }
    }
  }

  ?>