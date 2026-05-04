<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIBS - Login</title>
    
</head>

<style>
body {
  margin: 0;
  font-family: Arial;
  background: #0d1117;
  color: white;
}

/* Main layout */
.container {
  display: flex;
  height: 100vh;
}

/* Left side */
.left {
  width: 40%;
  background: #111827;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.left h2 {
  color: #38bdf8;
}

/* Right side */
.right {
  width: 60%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.right h2 {
  margin-bottom: 5px;
}

/* Inputs */
input {
  width: 250px;
  padding: 10px;
  margin: 8px 0;
  border-radius: 5px;
  border: none;
}


/* Buttons */
button {
  width: 260px;
  padding: 10px;
  margin-top: 10px;
  border: none;
  border-radius: 5px;
  background: #2563eb;
  color: white;
 
}

button:hover {
  background: #1e40af;
}
</style>

</head>

<body>

<div class="container">

  <!-- Left -->
  <div class="left">
    <h2>SMART INVENTORY</h2>
    <p>Secure Access Portal</p>
  </div>

  <!-- Right -->
  <div class="right">
    <h2>Welcome Back</h2>
    <p>Please enter your credentials</p>

    <input type="text" id="username" placeholder="Username / Email">
    <input type="password" id="password" placeholder="Password">

    <button onclick="login('admin')">LOG IN AS ADMIN</button>
    <button onclick="login('cashier')">LOG IN AS CASHIER</button>
  </div>

</div>


</body>
</html>