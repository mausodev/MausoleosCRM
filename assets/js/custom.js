/***********
***********
***********
	Bootstrap JS 
***********
***********
***********/

// Tooltip
var tooltipTriggerList = [].slice.call(
	document.querySelectorAll('[data-bs-toggle="tooltip"]')
);
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
	return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Popover
var popoverTriggerList = [].slice.call(
	document.querySelectorAll('[data-bs-toggle="popover"]')
);
var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
	return new bootstrap.Popover(popoverTriggerEl);
});

// Cargar script de mayúsculas para campos de texto
document.addEventListener('DOMContentLoaded', function() {
    // Crear elemento script para cargar el archivo de mayúsculas
    const script = document.createElement('script');
    script.src = 'assets/js/uppercase-fields.js';
    script.async = true;
    document.head.appendChild(script);
});
