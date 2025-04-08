<div class="container">
  <div class="header-section">
    <h1 class="title">Liste des Responsables</h1>
    <button class="nouveau-responsable-btn">
      <i class="fas fa-plus"></i>
      Nouveau Responsable
    </button>
  </div>

  <div class="filters-section">
    <div class="filter-group">
      <label>Service</label>
      <select id="filter-service">
        <option value="">Tous les services</option>
        <?php foreach($services as $service): ?>
          <option value="<?= $service->id ?>"><?= $service->nom ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    
    <div class="filter-group">
      <label>Ville</label>
      <select id="filter-ville">
        <option value="">Toutes les villes</option>
        <?php foreach($villes as $ville): ?>
          <option value="<?= $ville ?>"><?= $ville ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    
    <div class="filter-group">
      <label>Date d'entrée</label>
      <input type="date" id="filter-date" />
    </div>
    
    <div class="filters-buttons">
      <button class="apply-filters-btn">
        <i class="fas fa-filter"></i> Appliquer
      </button>
      <button class="reset-filters-btn">
        <i class="fas fa-undo"></i> Réinitialiser
      </button>
      <button class="export-btn">
        <i class="fas fa-file-export"></i> Exporter
      </button>
    </div>
  </div>

  <div class="search-box">
    <i class="fas fa-search"></i>
    <input type="text" id="search-responsable" placeholder="Rechercher un responsable...">
  </div>

  <!-- Messages de notification -->
  <div class="alert alert-success" style="display: none;">
    <i class="fas fa-check-circle"></i>
    <span class="alert-message"></span>
    <button class="close-btn"><i class="fas fa-times"></i></button>
  </div>

  <div class="alert alert-danger" style="display: none;">
    <i class="fas fa-exclamation-circle"></i>
    <span class="alert-message"></span>
    <button class="close-btn"><i class="fas fa-times"></i></button>
  </div>

  <div class="responsables-table">
    <div class="table-header">
      <div class="row">
        <div class="col">Photo</div>
        <div class="col">Matricule</div>
        <div class="col">Nom</div>
        <div class="col">Prénom</div>
        <div class="col">Email</div>
        <div class="col">Téléphone</div>
        <div class="col">Ville</div>
        <div class="col">Service</div>
        <div class="col">Actions</div>
      </div>
    </div>

    <div class="table-body">
      <?php if(empty($responsables)): ?>
        <div class="empty-state">
          <img src="assets/empty-state.svg" alt="Aucune donnée">
          <p>Aucun responsable trouvé</p>
        </div>
      <?php else: ?>
        <?php foreach($responsables as $responsable): ?>
          <div class="table-row">
            <!-- Contenu des cellules -->
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div> 