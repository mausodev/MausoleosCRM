// Function to load plazas
function loadPlazas() {
    fetch('controlador/get_plazas.php')
        .then(response => response.json())
        .then(data => {
            const plazaSelect = document.getElementById('plaza');
            plazaSelect.innerHTML = '<option value="">Seleccione una plaza</option>';
            data.forEach(plaza => {
                plazaSelect.innerHTML += `<option value="${plaza.id}">${plaza.nombre}</option>`;
            });
        })
        .catch(error => console.error('Error loading plazas:', error));
}

// Function to load asesores based on selected plaza
function loadAsesores(plazaId) {
    fetch(`controlador/get_asesores.php?plaza=${plazaId}`)
        .then(response => response.json())
        .then(data => {
            const asesorSelect = document.getElementById('id_asesor');
            asesorSelect.innerHTML = '<option value="">Seleccione un asesor</option>';
            data.forEach(asesor => {
                asesorSelect.innerHTML += `<option value="${asesor.id}">${asesor.nombre} ${asesor.apellido}</option>`;
            });
        })
        .catch(error => console.error('Error loading asesores:', error));
}

// Function to load leads table
function loadLeads() {
    fetch('controlador/get_leads.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#tabla_leads tbody');
            tbody.innerHTML = '';
            data.forEach(lead => {
                tbody.innerHTML += `
                    <tr>
                        <td>${lead.id}</td>
                        <td>${lead.fecha}</td>
                        <td>${lead.cliente_nombre} ${lead.cliente_apellido}</td>
                        <td>${lead.telefono}</td>
                        <td>${lead.nombre_asesor}</td>
                        <td>${lead.comentario}</td>
                        <td><span class="badge bg-${getStatusColor(lead.estatus)}">${lead.estatus}</span></td>
                        <td>${lead.plaza}</td>
                        <td>
                            <button class="btn btn-outline-primary btn-sm" onclick="editLead(${lead.id})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Editar">
                                <i class="icon-check-circle"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteLead(${lead.id})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Eliminar">
                                <i class="icon-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => console.error('Error loading leads:', error));
}

// Function to get status color
function getStatusColor(status) {
    switch(status) {
        case 'Nuevo': return 'info';
        case 'En Proceso': return 'warning';
        case 'Cerrado': return 'success';
        default: return 'secondary';
    }
}

// Function to save new lead
function saveLead() {
    const form = document.getElementById('newLeadForm');
    const formData = new FormData(form);
    
    fetch('controlador/save_lead.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Close modal and reload table
            const modal = bootstrap.Modal.getInstance(document.getElementById('newLeadModal'));
            modal.hide();
            loadLeads();
            form.reset();
        } else {
            alert('Error al guardar el lead: ' + data.message);
        }
    })
    .catch(error => console.error('Error saving lead:', error));
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadPlazas();
    loadLeads();

    // Plaza change event
    document.getElementById('plaza').addEventListener('change', function() {
        if(this.value) {
            loadAsesores(this.value);
        }
    });

    // Save lead button click
    document.getElementById('saveLead').addEventListener('click', saveLead);
}); 