function adjustMarkerWidths() {
    const markers = document.querySelectorAll('.vjs-marker');
    const totalWidth = 100; // Representing 100%
  
    markers.forEach((marker, index) => {
      const currentLeft = parseFloat(marker.style.left);
      let nextLeft = totalWidth;
  
      if (index < markers.length - 1) {
        nextLeft = parseFloat(markers[index + 1].style.left);
      }
  
      const width = nextLeft - currentLeft;
      marker.style.width = `calc(${width}% - 3px)`; 
      marker.classList.add('marker-width-changed');
    });
  }
  
  document.addEventListener("DOMContentLoaded", adjustMarkerWidths);
  