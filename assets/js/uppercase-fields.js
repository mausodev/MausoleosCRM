/**
 * Script para convertir automáticamente todos los campos de texto a mayúsculas
 * Se ejecuta en todos los formularios del sistema
 */

document.addEventListener('DOMContentLoaded', function() {
    // Función para convertir texto a mayúsculas
    function convertToUppercase(element) {
        if (element && element.value) {
            element.value = element.value.toUpperCase();
        }
    }

    // Función para aplicar mayúsculas a todos los campos de texto
    function applyUppercaseToTextFields() {
        // Seleccionar todos los campos de texto (input type="text")
        const textInputs = document.querySelectorAll('input[type="text"]');
        
        textInputs.forEach(input => {
            // Aplicar mayúsculas al valor actual si existe
            convertToUppercase(input);
            
            // Agregar event listeners para convertir en tiempo real
            input.addEventListener('input', function() {
                convertToUppercase(this);
            });
            
            // Agregar event listener para cuando se pierde el foco
            input.addEventListener('blur', function() {
                convertToUppercase(this);
            });
            
            // Agregar event listener para cuando se pega texto
            input.addEventListener('paste', function() {
                // Usar setTimeout para asegurar que el valor pegado se procese
                setTimeout(() => {
                    convertToUppercase(this);
                }, 10);
            });
        });
    }

    // Aplicar mayúsculas a campos de texto existentes
    applyUppercaseToTextFields();

    // Observer para detectar nuevos campos de texto que se agreguen dinámicamente
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                // Verificar si se agregaron nuevos nodos
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Buscar campos de texto en el nuevo nodo
                        const newTextInputs = node.querySelectorAll ? 
                            node.querySelectorAll('input[type="text"]') : [];
                        
                        newTextInputs.forEach(input => {
                            convertToUppercase(input);
                            
                            input.addEventListener('input', function() {
                                convertToUppercase(this);
                            });
                            
                            input.addEventListener('blur', function() {
                                convertToUppercase(this);
                            });
                            
                            input.addEventListener('paste', function() {
                                setTimeout(() => {
                                    convertToUppercase(this);
                                }, 10);
                            });
                        });

                        // También buscar textareas en el nuevo nodo
                        const newTextareas = node.querySelectorAll ? 
                            node.querySelectorAll('textarea') : [];
                        
                        newTextareas.forEach(textarea => {
                            if (textarea.value) {
                                textarea.value = textarea.value.toUpperCase();
                            }
                            
                            textarea.addEventListener('input', function() {
                                this.value = this.value.toUpperCase();
                            });
                            
                            textarea.addEventListener('blur', function() {
                                this.value = this.value.toUpperCase();
                            });
                            
                            textarea.addEventListener('paste', function() {
                                setTimeout(() => {
                                    this.value = this.value.toUpperCase();
                                }, 10);
                            });
                        });
                    }
                });
            }
        });
    });

    // Iniciar el observer para observar cambios en el DOM
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Función para aplicar mayúsculas a campos específicos por selector
    function applyUppercaseToSpecificFields(selector) {
        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            if (element.tagName === 'INPUT' && element.type === 'text') {
                convertToUppercase(element);
                
                element.addEventListener('input', function() {
                    convertToUppercase(this);
                });
                
                element.addEventListener('blur', function() {
                    convertToUppercase(this);
                });
                
                element.addEventListener('paste', function() {
                    setTimeout(() => {
                        convertToUppercase(this);
                    }, 10);
                });
            }
        });
    }

    // Aplicar mayúsculas a campos de textarea también (opcional)
    function applyUppercaseToTextareas() {
        const textareas = document.querySelectorAll('textarea');
        
        textareas.forEach(textarea => {
            // Aplicar mayúsculas al valor actual si existe
            if (textarea.value) {
                textarea.value = textarea.value.toUpperCase();
            }
            
            // Agregar event listeners
            textarea.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
            
            textarea.addEventListener('blur', function() {
                this.value = this.value.toUpperCase();
            });
            
            textarea.addEventListener('paste', function() {
                setTimeout(() => {
                    this.value = this.value.toUpperCase();
                }, 10);
            });
        });
    }

    // Aplicar mayúsculas a textareas
    applyUppercaseToTextareas();

    // Función global para aplicar mayúsculas manualmente si es necesario
    window.applyUppercaseToAllTextFields = function() {
        applyUppercaseToTextFields();
        applyUppercaseToTextareas();
    };

    // Función para aplicar mayúsculas a un formulario específico
    window.applyUppercaseToForm = function(formElement) {
        if (formElement) {
            const textInputs = formElement.querySelectorAll('input[type="text"], textarea');
            textInputs.forEach(input => {
                convertToUppercase(input);
            });
        }
    };

    // Función para manejar modales de Bootstrap
    function handleBootstrapModals() {
        // Escuchar cuando se muestran modales
        document.addEventListener('shown.bs.modal', function(event) {
            const modal = event.target;
            const textInputs = modal.querySelectorAll('input[type="text"], textarea');
            
            textInputs.forEach(input => {
                convertToUppercase(input);
                
                input.addEventListener('input', function() {
                    convertToUppercase(this);
                });
                
                input.addEventListener('blur', function() {
                    convertToUppercase(this);
                });
                
                input.addEventListener('paste', function() {
                    setTimeout(() => {
                        convertToUppercase(this);
                    }, 10);
                });
            });
        });
    }

    // Inicializar manejo de modales
    handleBootstrapModals();

    // Función para aplicar mayúsculas a campos específicos por ID
    window.applyUppercaseToField = function(fieldId) {
        const field = document.getElementById(fieldId);
        if (field && (field.type === 'text' || field.tagName === 'TEXTAREA')) {
            convertToUppercase(field);
            
            field.addEventListener('input', function() {
                convertToUppercase(this);
            });
            
            field.addEventListener('blur', function() {
                convertToUppercase(this);
            });
            
            field.addEventListener('paste', function() {
                setTimeout(() => {
                    convertToUppercase(this);
                }, 10);
            });
        }
    };

    console.log('Script de mayúsculas cargado correctamente');
});
