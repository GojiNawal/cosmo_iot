<!-- menu.php -->
<!DOCTYPE html>
<html>
<head>
  <title>OpenSenseMap Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px auto;
      max-width: 800px;
      padding: 20px;
      line-height: 1.6;
    }
    h1 {
      color: #2c3e50;
    }
    .menu {
      list-style-type: none;
      padding: 0;
    }
    .menu li {
      display: inline-block;
      margin-right: 15px;
    }
    .menu a {
      text-decoration: none;
      color: #3498db;
      font-size: 18px;
      font-weight: bold;
    }
    .menu a:hover {
      color: #1abc9c;
    }
  </style>
</head>
<body>

  <h1>OpenSenseMap Dashboard</h1>
  <p>This dashboard allows you to view real-time environmental sensor data from OpenSenseMap.</p>

  <ul class="menu">
    <li><a href="index.php">Home</a></li>
    <li><a href="box.php?country=london">London</a></li>
    <li><a href="box.php?country=france">France</a></li>
    <li><a href="box.php?country=spain">Spain</a></li>
  </ul>

</body>
</html>
