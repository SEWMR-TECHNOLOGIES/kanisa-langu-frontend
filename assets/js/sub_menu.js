document.addEventListener('DOMContentLoaded', function() {
  const submenuLinks = document.querySelectorAll('.sidebar-item.has-submenu > a');
  const submenus = document.querySelectorAll('.sidebar-item.has-submenu .submenu');

  // Function to close all submenus
  function closeAllSubmenus() {
    submenus.forEach(submenu => {
      submenu.style.display = 'none';
    });
  }

  // Add click event listener to each submenu link
  submenuLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      const submenu = this.nextElementSibling;

      if (submenu && submenu.classList.contains('submenu')) {
        // Check if the clicked submenu is already open
        if (submenu.style.display === 'block') {
          submenu.style.display = 'none';
        } else {
          // Close other submenus
          closeAllSubmenus();
          // Open the clicked submenu
          submenu.style.display = 'block';
        }
      }
    });
  });

  // Close submenu when clicking outside of the sidebar
  document.addEventListener('click', function(event) {
    const isClickInsideSidebar = document.querySelector('.left-sidebar').contains(event.target);

    if (!isClickInsideSidebar) {
      closeAllSubmenus();
    }
  });
});

function getLocalTimestamp() {
    let now = new Date();
    let formattedTimestamp = now.getFullYear() + '-' +
        String(now.getMonth() + 1).padStart(2, '0') + '-' +
        String(now.getDate()).padStart(2, '0') + ' ' +
        String(now.getHours()).padStart(2, '0') + ':' +
        String(now.getMinutes()).padStart(2, '0') + ':' +
        String(now.getSeconds()).padStart(2, '0');
    
    return formattedTimestamp;
}

