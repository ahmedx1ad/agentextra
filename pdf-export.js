/**
 * Module d'exportation PDF simplifié pour liste d'agents
 */

// Fonction principale d'exportation PDF
function exportToPDF(tableId, filename = 'Liste_Agents') {
    const table = document.getElementById(tableId);
    if (!table) {
        console.error("Table non trouvée:", tableId);
        return;
    }
    
    // Afficher un message de chargement
    if (typeof showToast === 'function') {
        showToast('Génération du PDF en cours...', 'info');
    } else {
        console.log('Génération du PDF en cours...');
    }
    
    // Vérifier si jsPDF est déjà chargé
    if (typeof window.jspdf !== 'undefined') {
        // Si jsPDF est déjà chargé, extraire les données et générer le PDF
        try {
            const agentData = extractAgentDataFromTable(table);
            generateSimplePDF(agentData, filename);
        } catch (error) {
            handleExportError(error, "Erreur lors de la génération du PDF");
        }
        return;
    }
    
    // Charger d'abord la bibliothèque principale jsPDF
    const jsPdfScript = document.createElement('script');
    jsPdfScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
    jsPdfScript.onload = function() {
        console.log('jsPDF chargé avec succès');
        
        // Puis charger le plugin autoTable
        const autoTableScript = document.createElement('script');
        autoTableScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js';
        autoTableScript.onload = function() {
            console.log('Plugin autoTable chargé avec succès');
            
            // Une fois les deux scripts chargés, extraire les données et générer le PDF
            try {
                const agentData = extractAgentDataFromTable(table);
                generateSimplePDF(agentData, filename);
            } catch (error) {
                handleExportError(error, "Erreur lors de la génération du PDF");
            }
        };
        autoTableScript.onerror = function(error) {
            console.error('Erreur lors du chargement du plugin autoTable:', error);
            if (typeof showToast === 'function') {
                showToast('Erreur: Impossible de charger le plugin jsPDF-autoTable. Vérifiez votre connexion Internet.', 'error');
            } else {
                alert('Erreur: Impossible de charger le plugin jsPDF-autoTable. Vérifiez votre connexion Internet.');
            }
        };
        document.head.appendChild(autoTableScript);
    };
    jsPdfScript.onerror = function(error) {
        console.error('Erreur lors du chargement de jsPDF:', error);
        if (typeof showToast === 'function') {
            showToast('Erreur: Impossible de charger jsPDF. Vérifiez votre connexion Internet.', 'error');
        } else {
            alert('Erreur: Impossible de charger jsPDF. Vérifiez votre connexion Internet.');
        }
    };
    document.head.appendChild(jsPdfScript);
}

// Fonction pour gérer les erreurs d'exportation de manière unifiée
function handleExportError(error, defaultMessage) {
    const errorMessage = error.message || "Erreur inconnue";
    console.error(`${defaultMessage}: ${errorMessage}`, error);
    
    // Analyser l'erreur pour donner des conseils plus précis
    let userMessage = `${defaultMessage}: ${errorMessage}`;
    
    // Suggérer des solutions selon le type d'erreur
    if (errorMessage.includes("Chart")) {
        userMessage += "\nConseil: Vérifiez que l'accès à cdnjs.cloudflare.com n'est pas bloqué par votre pare-feu.";
    } else if (errorMessage.includes("docx")) {
        userMessage += "\nConseil: Vérifiez que l'accès à unpkg.com n'est pas bloqué par votre pare-feu.";
    } else if (errorMessage.includes("FileSaver") || errorMessage.includes("saveAs")) {
        userMessage += "\nConseil: Essayez d'autoriser les téléchargements dans votre navigateur.";
    } else if (errorMessage.includes("undefined")) {
        userMessage += "\nConseil: Vérifiez que JavaScript est activé et que les scripts ne sont pas bloqués.";
    }
    
    if (typeof showToast === 'function') {
        showToast(userMessage, 'error');
    } else {
        alert(userMessage);
    }
}

// Fonction pour charger des scripts externes
function loadScript(url) {
    return new Promise((resolve, reject) => {
        // Vérifier si le script est déjà chargé pour éviter les doublons
        const existingScript = document.querySelector(`script[src="${url}"]`);
        if (existingScript) {
            console.log(`Script déjà chargé: ${url}`);
            resolve();
            return;
        }
        
        console.log(`Chargement du script: ${url}`);
        const script = document.createElement('script');
        script.src = url;
        
        // Ajouter un timeout pour éviter les attentes infinies
        const timeoutId = setTimeout(() => {
            reject(new Error(`Délai d'attente dépassé lors du chargement de ${url}`));
        }, 10000); // 10 secondes de timeout
        
        script.onload = () => {
            clearTimeout(timeoutId);
            console.log(`Script chargé avec succès: ${url}`);
            // Donner un peu de temps au script pour s'initialiser
            setTimeout(resolve, 100);
        };
        
        script.onerror = (e) => {
            clearTimeout(timeoutId);
            reject(new Error(`Erreur lors du chargement de ${url}: ${e.message || 'Erreur inconnue'}`));
        };
        
        document.head.appendChild(script);
    });
}

// Extraire les données des agents depuis le tableau HTML
function extractAgentDataFromTable(table) {
    const agents = [];
    const rows = table.querySelectorAll('tbody tr');
    
    // Vérifier si des lignes sont sélectionnées (cases à cocher)
    const selectedRows = table.querySelectorAll('tbody tr input[type="checkbox"]:checked');
    const hasSelection = selectedRows.length > 0;
    
    // Filtrer les lignes selon la sélection et la visibilité
    const filteredRows = Array.from(rows).filter(row => {
        // Si des lignes sont sélectionnées, ne prendre que celles-ci
        if (hasSelection) {
            const checkbox = row.querySelector('input[type="checkbox"]');
            return checkbox && checkbox.checked && row.style.display !== 'none' && !row.classList.contains('d-none');
        }
        // Sinon prendre toutes les lignes visibles
        return row.style.display !== 'none' && !row.classList.contains('d-none');
    });
    
    // Message si aucune ligne n'est sélectionnée
    if (hasSelection && filteredRows.length === 0) {
        if (typeof showToast === 'function') {
            showToast('Aucun agent sélectionné. Veuillez cocher au moins une ligne.', 'warning');
        } else {
            alert('Aucun agent sélectionné. Veuillez cocher au moins une ligne.');
        }
        return [];
    }
    
    filteredRows.forEach((row, index) => {
        const cells = row.querySelectorAll('td');
        // Ignorer la première colonne si c'est une case à cocher et la dernière si ce sont des actions
        const startIndex = cells[0]?.querySelector('input[type="checkbox"]') ? 1 : 0;
        const endIndex = cells[cells.length-1]?.classList.contains('text-center') ? cells.length-1 : cells.length;
        
        // Extraire les données réelles du tableau si disponibles
        const idCell = cells[startIndex]?.textContent.trim() || `AG${(index + 1).toString().padStart(8, '0')}`;
        
        // Pour le nom, vérifier s'il contient réellement une valeur
        let nomText = cells[startIndex + 1]?.textContent.trim() || `Agent ${index + 1}`;
        // Nettoyer le nom (enlever les espaces excessifs)
        nomText = nomText.replace(/\s+/g, ' ').trim();
        
        const serviceText = cells[startIndex + 2]?.textContent.trim() || "Service";
        
        // Récupérer les valeurs numériques si disponibles, sinon générer des données aléatoires
        let niveau, experience, taille, permis;
        
        try {
            niveau = parseInt(cells[startIndex + 3]?.textContent.trim()) || 0;
        } catch {
            niveau = 0;
        }
        
        try {
            experience = parseInt(cells[startIndex + 4]?.textContent.trim()) || 0;
        } catch {
            experience = 0;
        }
        
        // Récupérer la taille - convertir au format standard avec un point décimal
        try {
            let tailleText = cells[startIndex + 5]?.textContent.trim() || "0";
            // Nettoyer la valeur de la taille (remplacer virgule par point, etc.)
            tailleText = tailleText.replace(/,/g, '.').replace(/[^\d.]/g, '');
            taille = parseFloat(tailleText) || 0;
        } catch {
            taille = 0;
        }
        
        // Récupérer le permis - nettoyer et normaliser en booléen
        try {
            const permisText = cells[startIndex + 6]?.textContent.trim().toLowerCase() || '';
            permis = permisText.includes('oui');
        } catch {
            permis = false;
        }
        
        // Calculer un score basé sur le niveau et l'expérience
        const score = ((niveau * 2) + (experience / 10)).toFixed(1);
        
        // Créer l'objet agent avec les données disponibles ou simulées
        const agent = {
            id: idCell,
            nom: nomText,
            service: serviceText,
            niveau: niveau,
            experience: experience,
            taille: taille.toFixed(2), // Format uniforme avec 2 décimales
            permis: permis,
            score: score
        };
        
        agents.push(agent);
    });
    
    return agents;
}

// Fonction principale pour générer un PDF simple
function generateSimplePDF(agents, filename) {
    try {
        // Vérifier que des agents ont été fournis
        if (!agents || agents.length === 0) {
            throw new Error("Aucun agent à exporter.");
        }
        
        // Vérifier que jsPDF est bien chargé
        if (typeof window.jspdf === 'undefined') {
            throw new Error("La bibliothèque jsPDF n'est pas disponible");
        }
        
        // Créer un nouveau document PDF au format A4
        const { jsPDF } = window.jspdf;
        
        // Vérifier que jsPDF est une fonction constructeur valide
        if (typeof jsPDF !== 'function') {
            throw new Error("Le constructeur jsPDF n'est pas disponible ou n'est pas correctement initialisé");
        }
        
        const doc = new jsPDF({
            orientation: 'landscape', // Format paysage pour plus de lisibilité
            unit: 'mm',
            format: 'a4'
        });
        
        // Vérifier si autoTable est disponible
        if (typeof doc.autoTable !== 'function') {
            console.warn("Le plugin autoTable n'est pas disponible, tentative de l'attacher manuellement");
            
            if (typeof window.jspdf_autotable !== 'undefined') {
                // Essayer d'attacher manuellement le plugin
                window.jspdf_autotable.default(doc);
            } else {
                throw new Error("Le plugin autoTable n'est pas disponible. Veuillez recharger la page et réessayer.");
            }
        }
        
        // Définir les colonnes du tableau
        const tableColumn = ['#', 'ID', 'Nom', 'Service', 'Niveau', 'Expérience', 'Taille', 'Permis', 'Score'];
        
        // Pagination - exactement 10 agents par page
        const rowsPerPage = 10;
        const totalPages = Math.ceil(agents.length / rowsPerPage);
        
        // Boucle pour créer une page par groupe de 10 agents
        for (let pageIndex = 0; pageIndex < totalPages; pageIndex++) {
            // Si ce n'est pas la première page, en ajouter une nouvelle
            if (pageIndex > 0) {
                doc.addPage();
            }
            
            // Sélectionner les 10 agents pour cette page
            const startIdx = pageIndex * rowsPerPage;
            const endIdx = Math.min(startIdx + rowsPerPage, agents.length);
            const pageAgents = agents.slice(startIdx, endIdx);
            
            // Titre simple
            doc.setFontSize(14);
            doc.setTextColor(0, 0, 0);
            doc.text(`Liste des Agents - Page ${pageIndex + 1}/${totalPages}`, 15, 15);
            
            // Date d'exportation
            doc.setFontSize(10);
            const dateFormatted = new Date().toLocaleDateString('fr-FR');
            doc.text('Exporté le ' + dateFormatted, 15, 22);
            
            // Créer les lignes du tableau pour cette page
            const tableRows = [];
            pageAgents.forEach((agent, index) => {
                const formattedTaille = String(agent.taille).replace(',', '.'); // Assurer que le format est avec un point
                const formattedPermis = agent.permis ? 'Oui' : 'Non';
                
                const agentData = [
                    (startIdx + index + 1).toString(), // Numéro
                    agent.id,
                    agent.nom,
                    agent.service,
                    agent.niveau.toString(),
                    agent.experience.toString(),
                    formattedTaille + ' m',
                    formattedPermis,
                    agent.score
                ];
                tableRows.push(agentData);
            });
            
            try {
                // Générer le tableau simple avec autoTable
                doc.autoTable({
                    head: [tableColumn],
                    body: tableRows,
                    startY: 25,
                    theme: 'grid',
                    styles: { 
                        fontSize: 10,
                        cellPadding: 2,
                        overflow: 'linebreak',
                        lineWidth: 0.1
                    },
                    headStyles: { 
                        fillColor: [220, 220, 220],
                        textColor: [0, 0, 0],
                        fontStyle: 'bold'
                    },
                    // Styles de colonnes simples
                    columnStyles: {
                        0: { cellWidth: 8 },       // #
                        1: { cellWidth: 25 },      // ID
                        2: { cellWidth: 35 },      // Nom
                        3: { cellWidth: 25 },      // Service
                        4: { cellWidth: 15 },      // Niveau
                        5: { cellWidth: 20 },      // Expérience
                        6: { cellWidth: 15 },      // Taille
                        7: { cellWidth: 20 },      // Permis
                        8: { cellWidth: 15 }       // Score
                    }
                });
            } catch (tableError) {
                console.error("Erreur lors de la génération du tableau:", tableError);
                throw new Error("Erreur lors de la génération du tableau PDF. Détails: " + tableError.message);
            }
            
            // Pied de page simple avec numéro de page
            doc.setFontSize(8);
            doc.text(`Page ${pageIndex + 1} sur ${totalPages}`, doc.internal.pageSize.width / 2, doc.internal.pageSize.height - 10, { align: 'center' });
        }
        
        // Enregistrer le PDF
        try {
            doc.save(`${filename}.pdf`);
            
            console.log('PDF généré avec succès');
            
            // Afficher un message de confirmation
            setTimeout(() => {
                if (typeof showToast === 'function') {
                    showToast(`Le document PDF contenant ${agents.length} agent(s) a été généré et téléchargé avec succès`, 'success', 5000);
                } else {
                    alert(`Le document PDF contenant ${agents.length} agent(s) a été généré et téléchargé avec succès`);
                }
            }, 1000);
        } catch (saveError) {
            console.error("Erreur lors de l'enregistrement du PDF:", saveError);
            throw new Error("Erreur lors de l'enregistrement du PDF. Détails: " + saveError.message);
        }
    } catch (error) {
        console.error('Erreur lors de la génération du PDF:', error);
        if (typeof showToast === 'function') {
            showToast('Erreur lors de la génération du PDF: ' + error.message, 'error');
        } else {
            alert('Erreur lors de la génération du PDF: ' + error.message);
        }
    }
}

// Fonction pour créer une image du graphique de permis (camembert)
function createPermitChartImage(withPermit, withoutPermit) {
    return new Promise((resolve, reject) => {
        try {
            // Vérifier que Chart.js est chargé
            if (typeof Chart === 'undefined') {
                throw new Error("La bibliothèque Chart.js n'est pas disponible");
            }
            
            // Créer un canvas temporaire
            const canvas = document.createElement('canvas');
            canvas.width = 500;
            canvas.height = 400;
            
            // Ajouter le canvas au document de façon cachée
            canvas.style.position = 'absolute';
            canvas.style.left = '-9999px';
            document.body.appendChild(canvas);
            
            // Créer le graphique
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error("Impossible d'obtenir le contexte 2D du canvas");
            }
            
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Avec permis', 'Sans permis'],
                    datasets: [{
                        data: [withPermit, withoutPermit],
                        backgroundColor: ['#4CAF50', '#F44336'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Répartition des agents selon le permis',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            
            // Convertir le canvas en image et résoudre la promesse
            setTimeout(() => {
                try {
                    const imageUrl = canvas.toDataURL('image/png').replace('data:image/png;base64,', '');
                    document.body.removeChild(canvas);
                    resolve(imageUrl);
                } catch (error) {
                    reject(new Error(`Erreur lors de la conversion du graphique en image: ${error.message}`));
                }
            }, 500);
        } catch (error) {
            reject(error);
        }
    });
}

// Fonction pour créer une image du graphique de répartition par service (barres)
function createServiceDistributionChart(serviceGroups) {
    return new Promise((resolve, reject) => {
        try {
            // Vérifier que Chart.js est chargé
            if (typeof Chart === 'undefined') {
                throw new Error("La bibliothèque Chart.js n'est pas disponible");
            }
            
            // Créer un canvas temporaire
            const canvas = document.createElement('canvas');
            canvas.width = 500;
            canvas.height = 400;
            
            // Ajouter le canvas au document de façon cachée
            canvas.style.position = 'absolute';
            canvas.style.left = '-9999px';
            document.body.appendChild(canvas);
            
            // Trier les services par nombre d'agents
            const sortedServices = Object.entries(serviceGroups)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 10); // Limiter aux 10 principaux services
            
            // Créer le graphique
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error("Impossible d'obtenir le contexte 2D du canvas");
            }
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: sortedServices.map(item => item[0]),
                    datasets: [{
                        label: 'Nombre d\'agents',
                        data: sortedServices.map(item => item[1]),
                        backgroundColor: '#2196F3',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre d\'agents'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Service'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Répartition des agents par service',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Convertir le canvas en image et résoudre la promesse
            setTimeout(() => {
                try {
                    const imageUrl = canvas.toDataURL('image/png').replace('data:image/png;base64,', '');
                    document.body.removeChild(canvas);
                    resolve(imageUrl);
                } catch (error) {
                    reject(new Error(`Erreur lors de la conversion du graphique en image: ${error.message}`));
                }
            }, 500);
        } catch (error) {
            reject(error);
        }
    });
}

// Fonction pour créer une image du graphique de répartition par expérience (barres)
function createExperienceDistributionChart(experienceGroups) {
    return new Promise((resolve, reject) => {
        try {
            // Vérifier que Chart.js est chargé
            if (typeof Chart === 'undefined') {
                throw new Error("La bibliothèque Chart.js n'est pas disponible");
            }
            
            // Créer un canvas temporaire
            const canvas = document.createElement('canvas');
            canvas.width = 500;
            canvas.height = 400;
            
            // Ajouter le canvas au document de façon cachée
            canvas.style.position = 'absolute';
            canvas.style.left = '-9999px';
            document.body.appendChild(canvas);
            
            // Trier les tranches d'expérience
            const sortedExperience = Object.entries(experienceGroups)
                .sort((a, b) => {
                    const numA = parseInt(a[0].split('-')[0]);
                    const numB = parseInt(b[0].split('-')[0]);
                    return numA - numB;
                });
            
            // Créer le graphique
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error("Impossible d'obtenir le contexte 2D du canvas");
            }
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: sortedExperience.map(item => item[0] + ' ans'),
                    datasets: [{
                        label: 'Nombre d\'agents',
                        data: sortedExperience.map(item => item[1]),
                        backgroundColor: '#FF9800',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre d\'agents'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Expérience (années)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Répartition des agents par expérience',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Convertir le canvas en image et résoudre la promesse
            setTimeout(() => {
                try {
                    const imageUrl = canvas.toDataURL('image/png').replace('data:image/png;base64,', '');
                    document.body.removeChild(canvas);
                    resolve(imageUrl);
                } catch (error) {
                    reject(new Error(`Erreur lors de la conversion du graphique en image: ${error.message}`));
                }
            }, 500);
        } catch (error) {
            reject(error);
        }
    });
}

// Fonction pour créer une image du graphique des 5 meilleurs agents (barres horizontales)
function createTopAgentsChart(agents) {
    return new Promise((resolve, reject) => {
        try {
            // Vérifier que Chart.js est chargé
            if (typeof Chart === 'undefined') {
                throw new Error("La bibliothèque Chart.js n'est pas disponible");
            }
            
            // Créer un canvas temporaire
            const canvas = document.createElement('canvas');
            canvas.width = 500;
            canvas.height = 400;
            
            // Ajouter le canvas au document de façon cachée
            canvas.style.position = 'absolute';
            canvas.style.left = '-9999px';
            document.body.appendChild(canvas);
            
            // Sélectionner les 5 meilleurs agents par score
            const topAgents = [...agents]
                .sort((a, b) => parseFloat(b.score) - parseFloat(a.score))
                .slice(0, 5);
            
            // Créer le graphique
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error("Impossible d'obtenir le contexte 2D du canvas");
            }
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: topAgents.map(agent => agent.nom),
                    datasets: [{
                        axis: 'y',
                        label: 'Score',
                        data: topAgents.map(agent => parseFloat(agent.score)),
                        backgroundColor: '#9C27B0',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Score'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Top 5 des agents par score',
                            font: {
                                size: 16
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Convertir le canvas en image et résoudre la promesse
            setTimeout(() => {
                try {
                    const imageUrl = canvas.toDataURL('image/png').replace('data:image/png;base64,', '');
                    document.body.removeChild(canvas);
                    resolve(imageUrl);
                } catch (error) {
                    reject(new Error(`Erreur lors de la conversion du graphique en image: ${error.message}`));
                }
            }, 500);
        } catch (error) {
            reject(error);
        }
    });
}

// Fonction alternative pour exporter en PDF en cas d'échec du chargement de jsPDF
function exportTableToPDFAlternative(tableId, filename = 'Liste_Agents') {
    try {
        const table = document.getElementById(tableId);
        if (!table) {
            throw new Error("Tableau non trouvé");
        }
        
        // Afficher un message de chargement
        if (typeof showToast === 'function') {
            showToast('Préparation de l\'export PDF...', 'info');
        }
        
        // Vérifier si des lignes sont sélectionnées (cases à cocher)
        const selectedRows = table.querySelectorAll('tbody tr input[type="checkbox"]:checked');
        const hasSelection = selectedRows.length > 0;
        
        // Créer une copie du tableau pour l'exportation
        const exportTable = table.cloneNode(true);
        
        // Filtrer les lignes selon la sélection
        const tbody = exportTable.querySelector('tbody');
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }
        
        let rowsToExport = [];
        
        if (hasSelection) {
            // Récupérer uniquement les lignes sélectionnées
            selectedRows.forEach(checkbox => {
                const row = checkbox.closest('tr');
                if (row) {
                    const clonedRow = row.cloneNode(true);
                    tbody.appendChild(clonedRow);
                    rowsToExport.push(clonedRow);
                }
            });
            
            // Si aucune ligne n'est sélectionnée après filtrage, afficher un message
            if (rowsToExport.length === 0) {
                throw new Error("Aucun agent sélectionné n'est visible. Veuillez modifier vos filtres ou sélectionner d'autres agents.");
            }
        } else {
            // Récupérer toutes les lignes visibles
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                if (row.style.display !== 'none' && !row.classList.contains('d-none')) {
                    const clonedRow = row.cloneNode(true);
                    tbody.appendChild(clonedRow);
                    rowsToExport.push(clonedRow);
                }
            });
            
            // Si aucune ligne n'est visible, afficher un message
            if (rowsToExport.length === 0) {
                throw new Error("Aucun agent n'est visible. Veuillez modifier vos filtres.");
            }
        }
        
        // Supprimer les colonnes non nécessaires
        const headerRow = exportTable.querySelector('thead tr');
        if (headerRow) {
            // Supprimer la première colonne (case à cocher) et la dernière (actions)
            if (headerRow.firstElementChild) headerRow.removeChild(headerRow.firstElementChild);
            if (headerRow.lastElementChild) headerRow.removeChild(headerRow.lastElementChild);
            
            // Supprimer les mêmes colonnes pour chaque ligne
            rowsToExport.forEach(row => {
                if (row.firstElementChild) row.removeChild(row.firstElementChild);
                if (row.lastElementChild) row.removeChild(row.lastElementChild);
            });
        }
        
        // Créer un conteneur avec du style pour le PDF
        const container = document.createElement('div');
        container.style.padding = '20px';
        
        // Ajouter un titre
        const title = document.createElement('h2');
        title.textContent = `Liste des Agents - ${hasSelection ? rowsToExport.length + ' sélectionné(s)' : 'Tous'}`;
        title.style.marginBottom = '15px';
        title.style.color = '#333';
        container.appendChild(title);
        
        // Ajouter la date d'exportation
        const date = document.createElement('p');
        date.textContent = 'Exporté le ' + new Date().toLocaleDateString();
        date.style.marginBottom = '15px';
        date.style.color = '#666';
        container.appendChild(date);
        
        // Appliquer des styles CSS au tableau
        exportTable.style.width = '100%';
        exportTable.style.borderCollapse = 'collapse';
        exportTable.style.fontSize = '10px';
        
        // Ajouter des styles pour les cellules
        const style = document.createElement('style');
        style.textContent = `
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            @media print {
                body { margin: 0; padding: 15px; }
                h2 { font-size: 18px; margin-bottom: 10px; }
                table { font-size: 12px; }
                th, td { padding: 5px; }
            }
        `;
        container.appendChild(style);
        
        // Ajouter le tableau
        container.appendChild(exportTable);
        
        // Nettoyer les éléments précédents si présents
        const oldPrintSection = document.getElementById('printSection');
        if (oldPrintSection) {
            document.body.removeChild(oldPrintSection);
        }
        
        const oldPrintCSS = document.getElementById('printSectionCSS');
        if (oldPrintCSS) {
            document.head.removeChild(oldPrintCSS);
        }
        
        // Créer un Print CSS
        const printCSS = document.createElement('style');
        printCSS.id = 'printSectionCSS';
        printCSS.textContent = `
            @media print {
                body * { visibility: hidden; }
                #printSection, #printSection * { visibility: visible; }
                #printSection { position: absolute; left: 0; top: 0; width: 100%; }
            }
        `;
        document.head.appendChild(printCSS);
        
        // Créer une div pour l'impression et l'ajouter au document
        const printSection = document.createElement('div');
        printSection.id = 'printSection';
        printSection.appendChild(container.cloneNode(true));
        document.body.appendChild(printSection);
        
        // Afficher un message de chargement
        if (typeof showToast === 'function') {
            showToast('Ouverture de la boîte de dialogue d\'impression...', 'info');
            showToast('👉 Utilisez "Enregistrer au format PDF" ou "Imprimer" dans la boîte de dialogue', 'info', 10000);
        }
        
        // Imprimer avec un délai pour s'assurer que tout est chargé
        setTimeout(() => {
            try {
                window.print();
                
                // Afficher un message de confirmation
                setTimeout(() => {
                    if (typeof showToast === 'function') {
                        showToast(`Export de ${rowsToExport.length} agent(s) réussi.`, 'success', 5000);
                    }
                }, 1000);
            } catch (printError) {
                console.error('Erreur lors de l\'impression:', printError);
                if (typeof showToast === 'function') {
                    showToast('Erreur lors de l\'impression: ' + printError.message, 'error');
                }
            }
        }, 500);
    } catch (error) {
        console.error('Erreur lors de l\'export PDF alternatif:', error);
        if (typeof showToast === 'function') {
            showToast('Erreur: ' + error.message, 'error', 5000);
        } else {
            alert('Erreur: ' + error.message);
        }
    }
}

// Fonction pour vérifier la disponibilité de jsPDF et utiliser une alternative si nécessaire
function smartExportToPDF(tableId, filename = 'Liste_Agents') {
    // Utiliser directement la méthode alternative qui est plus fiable
    exportTableToPDFAlternative(tableId, filename);
}

// Initialiser l'exportation PDF lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    const exportPDFBtn = document.getElementById('exportPDF');
    if (exportPDFBtn) {
        exportPDFBtn.addEventListener('click', function() {
            smartExportToPDF('agentsTable', 'Liste_Agents');
        });
    }
}); 