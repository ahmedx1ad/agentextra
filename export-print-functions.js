/**
 * Fonctions d'exportation et d'impression pour la liste des agents
 */

// Fonction pour initialiser les fonctionnalités d'exportation et d'impression
function setupExportAndPrintFunctions() {
    // Boutons d'exportation et d'impression
    const exportExcelBtn = document.getElementById('exportExcel');
    const exportPDFBtn = document.getElementById('exportPDF');
    const exportCSVBtn = document.getElementById('exportCSV');
    const printListBtn = document.getElementById('printList');
    
    // Gestion des cases à cocher
    const selectAllAgentsCheckbox = document.getElementById('selectAllAgents');
    const agentCheckboxes = document.querySelectorAll('.agent-checkbox');
    
    // Exportation vers Excel
    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', function() {
            exportTableToExcel('agentsTable', 'Liste_Agents');
        });
    }
    
    // Exportation vers PDF
    if (exportPDFBtn) {
        exportPDFBtn.addEventListener('click', function() {
            exportTableToPDF('agentsTable', 'Liste_Agents');
        });
    }
    
    // Exportation vers CSV
    if (exportCSVBtn) {
        exportCSVBtn.addEventListener('click', function() {
            exportTableToCSV('agentsTable', 'Liste_Agents');
        });
    }
    
    // Impression
    if (printListBtn) {
        printListBtn.addEventListener('click', function() {
            printTable('agentsTable');
        });
    }
    
    // Gestion de la sélection de tous les agents
    if (selectAllAgentsCheckbox) {
        selectAllAgentsCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            // Cocher/décocher toutes les cases
            agentCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                
                // Mettre à jour la classe de la ligne pour la sélection
                const row = checkbox.closest('tr');
                if (row) {
                    row.classList.toggle('table-selected', isChecked);
                }
            });
            
            // Mettre à jour les boutons d'action
            updateBulkActionButtons();
        });
    }
    
    // Gestion des cases à cocher individuelles
    agentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Mettre à jour la classe de la ligne
            const row = this.closest('tr');
            if (row) {
                row.classList.toggle('table-selected', this.checked);
            }
            
            // Vérifier si toutes les cases sont cochées pour mettre à jour la case "Tout sélectionner"
            if (selectAllAgentsCheckbox) {
                const totalCheckboxes = agentCheckboxes.length;
                const checkedCheckboxes = document.querySelectorAll('.agent-checkbox:checked').length;
                
                selectAllAgentsCheckbox.checked = totalCheckboxes === checkedCheckboxes;
                selectAllAgentsCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
            }
            
            // Mettre à jour les boutons d'action
            updateBulkActionButtons();
        });
    });
    
    // Initialisation des compteurs et boutons
    updateBulkActionButtons();
}

// Fonction pour mettre à jour les boutons d'actions groupées
function updateBulkActionButtons() {
    const selectedCount = document.querySelectorAll('.agent-checkbox:checked').length;
    const bulkActionButtons = document.querySelectorAll('.bulk-action-btn');
    const selectedCountDisplay = document.getElementById('selectedAgentsCount');
    
    // Afficher/masquer les boutons selon qu'il y a des éléments sélectionnés
    bulkActionButtons.forEach(btn => {
        btn.disabled = selectedCount === 0;
    });
    
    // Mettre à jour le compteur d'éléments sélectionnés
    if (selectedCountDisplay) {
        selectedCountDisplay.textContent = selectedCount;
    }
}

// Fonction pour exporter le tableau vers Excel
function exportTableToExcel(tableId, filename = '') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Créer une copie du tableau pour l'exportation
    const exportTable = table.cloneNode(true);
    
    // Supprimer les colonnes non nécessaires pour l'exportation
    // Par exemple, supprimer la colonne de cases à cocher et la colonne d'actions
    const headerRow = exportTable.querySelector('thead tr');
    const rows = exportTable.querySelectorAll('tbody tr');
    
    if (headerRow) {
        // Supprimer la première colonne (case à cocher) et la dernière (actions)
        headerRow.removeChild(headerRow.firstElementChild);
        headerRow.removeChild(headerRow.lastElementChild);
        
        // Supprimer les mêmes colonnes pour chaque ligne
        rows.forEach(row => {
            if (row.firstElementChild) row.removeChild(row.firstElementChild);
            if (row.lastElementChild) row.removeChild(row.lastElementChild);
        });
    }
    
    // Convertir le tableau en HTML
    let html = exportTable.outerHTML;
    
    // Application de styles
    html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Agents</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>' + html + '</body></html>';
    
    // Créer un Blob et le télécharger
    const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.xls';
    link.click();
    
    // Afficher un message de confirmation
    if (typeof showToast === 'function') {
        showToast('Exportation Excel réussie', 'success');
    }
}

// Fonction pour exporter le tableau vers PDF
function exportTableToPDF(tableId, filename = '') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Afficher un message de chargement
    if (typeof showToast === 'function') {
        showToast('Génération du PDF en cours...', 'info');
    }
    
    // Utiliser html2pdf.js (il faut l'ajouter à la page)
    if (typeof html2pdf === 'undefined') {
        // Si html2pdf n'est pas chargé, on l'ajoute dynamiquement
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = function() {
            exportToPDFWithHtml2pdf(table, filename);
        };
        script.onerror = function() {
            if (typeof showToast === 'function') {
                showToast('Erreur lors du chargement de la bibliothèque PDF. Veuillez réessayer.', 'error');
            } else {
                alert('Erreur lors du chargement de la bibliothèque PDF. Veuillez réessayer.');
            }
        };
        document.head.appendChild(script);
    } else {
        exportToPDFWithHtml2pdf(table, filename);
    }
}

function exportToPDFWithHtml2pdf(table, filename) {
    try {
    // Créer une copie du tableau pour l'exportation
    const exportTable = table.cloneNode(true);
        
        // Ne conserver que les lignes visibles
        const visibleRows = Array.from(exportTable.querySelectorAll('tbody tr')).filter(row => 
            row.style.display !== 'none');
        
        // Supprimer toutes les lignes du tbody
        const tbody = exportTable.querySelector('tbody');
        tbody.innerHTML = '';
        
        // Réajouter uniquement les lignes visibles
        visibleRows.forEach(row => tbody.appendChild(row.cloneNode(true)));
    
    // Supprimer les colonnes non nécessaires
    const headerRow = exportTable.querySelector('thead tr');
    const rows = exportTable.querySelectorAll('tbody tr');
    
    if (headerRow) {
        // Supprimer la première colonne (case à cocher) et la dernière (actions)
        headerRow.removeChild(headerRow.firstElementChild);
        headerRow.removeChild(headerRow.lastElementChild);
        
        // Supprimer les mêmes colonnes pour chaque ligne
        rows.forEach(row => {
            if (row.firstElementChild) row.removeChild(row.firstElementChild);
            if (row.lastElementChild) row.removeChild(row.lastElementChild);
        });
    }
    
    // Créer un conteneur avec du style pour le PDF
    const container = document.createElement('div');
    container.style.padding = '20px';
        container.style.fontFamily = 'Arial, sans-serif';
        
        // Créer l'en-tête avec le logo LPS
        const header = document.createElement('div');
        header.style.display = 'flex';
        header.style.justifyContent = 'space-between';
        header.style.alignItems = 'center';
        header.style.marginBottom = '20px';
        header.style.borderBottom = '2px solid #0056b3';
        header.style.paddingBottom = '10px';
        
        // Logo LPS (base64 encoded pour garantir qu'il soit inclus)
        // Note: remplacer cette image par le vrai logo LPS en base64
        const logoBase64 = 'data:image/png;base64,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';
        
        const logo = document.createElement('img');
        logo.src = logoBase64;
        logo.style.height = '60px';
        logo.style.marginRight = '20px';
        header.appendChild(logo);
        
        // Titre et date
        const titleContainer = document.createElement('div');
        titleContainer.style.flexGrow = '1';
        
    const title = document.createElement('h2');
    title.textContent = 'Liste des Agents';
        title.style.margin = '0 0 5px 0';
    title.style.color = '#333';
        title.style.fontSize = '24px';
        titleContainer.appendChild(title);
    
    const date = document.createElement('p');
    date.textContent = 'Exporté le ' + new Date().toLocaleDateString();
        date.style.margin = '0';
    date.style.color = '#666';
        date.style.fontSize = '14px';
        titleContainer.appendChild(date);
        
        header.appendChild(titleContainer);
        container.appendChild(header);
        
        // Ajouter des informations supplémentaires
        const infoBox = document.createElement('div');
        infoBox.style.backgroundColor = '#f8f8f8';
        infoBox.style.border = '1px solid #ddd';
        infoBox.style.borderRadius = '4px';
        infoBox.style.padding = '10px';
        infoBox.style.marginBottom = '15px';
        
        const infoTitle = document.createElement('h3');
        infoTitle.textContent = 'Information';
        infoTitle.style.margin = '0 0 5px 0';
        infoTitle.style.fontSize = '16px';
        infoTitle.style.color = '#0056b3';
        infoBox.appendChild(infoTitle);
        
        const infoText = document.createElement('p');
        infoText.textContent = 'Ce document présente la liste des agents classés selon les critères sélectionnés. Il est généré automatiquement par le système de gestion du personnel.';
        infoText.style.margin = '0';
        infoText.style.fontSize = '12px';
        infoBox.appendChild(infoText);
        
        container.appendChild(infoBox);
    
    // Appliquer des styles CSS au tableau
    exportTable.style.width = '100%';
    exportTable.style.borderCollapse = 'collapse';
        exportTable.style.fontSize = '12px';
        exportTable.style.marginBottom = '20px';
        
        // Appliquer des styles aux cellules du tableau
        const allCells = exportTable.querySelectorAll('th, td');
        allCells.forEach(cell => {
            cell.style.border = '1px solid #ddd';
            cell.style.padding = '8px';
            cell.style.textAlign = 'left';
        });
        
        // Styliser les en-têtes
        const headers = exportTable.querySelectorAll('th');
        headers.forEach(header => {
            header.style.backgroundColor = '#0056b3';
            header.style.color = 'white';
            header.style.fontWeight = 'bold';
        });
        
        // Styliser les lignes alternées
        const bodyRows = exportTable.querySelectorAll('tbody tr');
        bodyRows.forEach((row, index) => {
            if (index % 2 === 0) {
                row.style.backgroundColor = '#f9f9f9';
            }
        });
    
    // Ajouter le tableau
    container.appendChild(exportTable);
    
        // Ajouter un pied de page
        const footer = document.createElement('div');
        footer.style.borderTop = '1px solid #ddd';
        footer.style.marginTop = '20px';
        footer.style.paddingTop = '10px';
        footer.style.fontSize = '10px';
        footer.style.color = '#666';
        footer.style.textAlign = 'center';
        
        const footerText = document.createElement('p');
        footerText.innerHTML = 'Document confidentiel - LPS &copy; ' + new Date().getFullYear() + ' - Tous droits réservés';
        footerText.style.margin = '0';
        footer.appendChild(footerText);
        
        container.appendChild(footer);
        
        // Options pour html2pdf avec un design professionnel
    const options = {
            margin: [15, 15, 15, 15],
        filename: filename + '.pdf',
            image: { type: 'jpeg', quality: 1.0 },
            html2canvas: { 
                scale: 2,
                useCORS: true,
                logging: false
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a4', 
                orientation: 'landscape',
                compress: true
            },
            pagebreak: { mode: 'avoid-all' }
        };
        
        // Générer le PDF avec gestion des erreurs
        html2pdf()
            .from(container)
            .set(options)
            .save()
            .then(() => {
        if (typeof showToast === 'function') {
            showToast('PDF généré avec succès', 'success');
        }
            })
            .catch(error => {
                console.error('Erreur lors de la génération du PDF:', error);
                if (typeof showToast === 'function') {
                    showToast('Erreur lors de la génération du PDF: ' + error.message, 'error');
                } else {
                    alert('Erreur lors de la génération du PDF: ' + error.message);
                }
            });
    } catch (error) {
        console.error('Erreur inattendue:', error);
        if (typeof showToast === 'function') {
            showToast('Erreur inattendue: ' + error.message, 'error');
        } else {
            alert('Erreur inattendue: ' + error.message);
        }
    }
}

// Fonction pour exporter le tableau vers CSV
function exportTableToCSV(tableId, filename = '') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Récupérer les en-têtes (sauf case à cocher et actions)
    const headers = Array.from(table.querySelectorAll('thead th')).slice(1, -1);
    const headerTitles = headers.map(header => header.innerText.trim());
    
    // Récupérer les lignes visibles
    const rows = Array.from(table.querySelectorAll('tbody tr'))
        .filter(row => row.style.display !== 'none')
        .map(row => {
            // Récupérer toutes les cellules sauf la première (checkbox) et la dernière (actions)
            const cells = Array.from(row.querySelectorAll('td')).slice(1, -1);
            return cells.map(cell => {
                // Nettoyer le texte (supprimer les sauts de ligne, virgules, etc.)
                let text = cell.innerText.replace(/[\n\r]+/g, ' ').trim();
                
                // Si le texte contient une virgule, le mettre entre guillemets
                if (text.includes(',')) {
                    text = `"${text}"`;
                }
                
                return text;
            });
        });
    
    // Construire le contenu CSV
    let csv = headerTitles.join(',') + '\n';
    rows.forEach(row => {
        csv += row.join(',') + '\n';
    });
    
    // Créer un Blob et le télécharger
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.csv';
    link.click();
    
    // Afficher un message de confirmation
    if (typeof showToast === 'function') {
        showToast('Exportation CSV réussie', 'success');
    }
}

// Fonction pour imprimer le tableau
function printTable(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Créer une fenêtre d'impression
    const printWindow = window.open('', '_blank');
    const title = 'Liste des Agents - ' + new Date().toLocaleDateString();
    
    // Créer le HTML pour l'impression
    let printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title}</title>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #333; margin-bottom: 20px; font-size: 24px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .print-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
                .print-date { color: #666; font-size: 14px; }
                @media print {
                    body { margin: 0; padding: 15px; }
                    h1 { font-size: 18px; margin-bottom: 10px; }
                    table { font-size: 12px; }
                    th, td { padding: 5px; }
                }
            </style>
        </head>
        <body>
            <div class="print-header">
                <h1>${title}</h1>
                <div class="print-date">Imprimé le ${new Date().toLocaleString()}</div>
            </div>
    `;
    
    // Créer une copie du tableau pour l'impression
    const printTable = table.cloneNode(true);
    
    // Supprimer les colonnes non nécessaires
    const headerRow = printTable.querySelector('thead tr');
    const rows = printTable.querySelectorAll('tbody tr');
    
    if (headerRow) {
        // Supprimer la première colonne (case à cocher) et la dernière (actions)
        headerRow.removeChild(headerRow.firstElementChild);
        headerRow.removeChild(headerRow.lastElementChild);
        
        // Supprimer les mêmes colonnes pour chaque ligne visible
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                if (row.firstElementChild) row.removeChild(row.firstElementChild);
                if (row.lastElementChild) row.removeChild(row.lastElementChild);
            } else {
                // Cacher les lignes non visibles
                row.style.display = 'none';
            }
        });
    }
    
    // Ajouter le tableau au contenu d'impression
    printContent += printTable.outerHTML;
    printContent += '</body></html>';
    
    // Écrire dans la fenêtre d'impression et imprimer
    printWindow.document.open();
    printWindow.document.write(printContent);
    printWindow.document.close();
    
    // Attendre que tout soit chargé
    printWindow.onload = function() {
        printWindow.print();
        printWindow.onafterprint = function() {
            printWindow.close();
        };
    };
}

// Initialiser les fonctionnalités lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    setupExportAndPrintFunctions();
}); 