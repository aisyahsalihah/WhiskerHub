describe('WhiskerHub - REQ-008: Pembelian dan Pengurusan Produk', () => {

  const validEmail = 'test@whiskerhub.com';
  const validPassword = '123456';

  beforeEach(() => {
    // Log masuk terlebih dahulu sebelum mengakses shopping.php
    cy.visit('/view/login.php');
    cy.get('#email').type(validEmail);
    cy.get('#password').type(validPassword);
    cy.get('form#loginForm').submit();
    cy.url().should('include', '/view/mainmenu.php');
  });

  it('Langkah 1: Validasi Komponen Halaman Shopping', () => {
    // Melawat halaman shopping.php
    cy.visit('/view/shopping.php');

    // Semak elemen-elemen penting
    cy.contains('WhiskerShop').should('be.visible');
    cy.get('#searchInput').should('exist');
    cy.get('#productGrid').should('exist');
  });

  it('Langkah 2: Interaksi Carian Produk', () => {
    cy.visit('/view/shopping.php');

    // Tunggu senarai produk selesai diambil
    cy.get('#productGrid').should('not.contain', 'Loading products...');

    // Masukkan kata kunci carian dan lakukan carian
    cy.get('#searchInput').type('makanan');
    cy.get('button').contains('SEARCH').click();

    // Pastikan UI dikemas kini sama ada dengan produk bertapis atau mesej "Tiada produk dijumpai."
    cy.get('#productGrid').then(($grid) => {
      if ($grid.find('.product-card').length > 0) {
        cy.get('.product-card').first().should('be.visible');
      } else {
        cy.contains('Tiada produk dijumpai.').should('be.visible');
      }
    });
  });

  it('Langkah 3: Tambah ke Troli melalui Modal Produk', () => {
    cy.visit('/view/shopping.php');

    // Tunggu produk selesai diambil
    cy.get('#productGrid').should('not.contain', 'Loading products...');

    cy.get('body').then(($body) => {
      if ($body.find('.product-card').length > 0) {
        // Klik pada kad produk pertama untuk membuka modal
        cy.get('.product-card').first().click();

        // Sahkan modal produk dipaparkan secara interaktif
        cy.get('#productModal').should('be.visible');
        cy.get('#modalTitle').should('not.be.empty');

        // Sediakan stub untuk menangkap mesej alert pengesahan
        const alertStub = cy.stub();
        cy.on('window:alert', alertStub);

        // Klik butang tambah ke troli
        cy.get('.btn-add-cart').click();

        // Semak modal ditutup dan alert dipicu
        cy.wrap(alertStub).should('have.been.called');
        cy.get('#productModal').should('not.be.visible');
      } else {
        cy.log('Tiada rekod produk untuk diuji buat masa ini.');
      }
    });
  });

});
