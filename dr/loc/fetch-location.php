<script>
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(
    function(position) {

      fetch('save-location.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          lat: position.coords.latitude,
          lon: position.coords.longitude
        })
      })
      .then(response => response.text())
      .then(city => {
        // console.log("Detected city:", city);
        // document.write(city);
      });

    },
    function(error) {
      console.log("Location denied", error);
    }
  );
}
</script>

<!-- <p>Your city: <strong id="city">Detecting...</strong></p> -->
