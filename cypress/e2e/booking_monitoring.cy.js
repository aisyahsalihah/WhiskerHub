describe('WhiskerHub - REQ-007: Tindakan dan Pemantauan Tempahan Aktif', () => {

  const validEmail = 'test@whiskerhub.com';
  const validPassword = '123456';

  beforeEach(() => {
    // Log masuk terlebih dahulu
    cy.visit('/view/login.php');
    cy.get('#email').type(validEmail);
    cy.get('#password').type(validPassword);
    cy.get('form#loginForm').submit();
    cy.url().should('include', '/view/mainmenu.php');
  });

  it('Langkah 1: Layari dashboard tempahan aktif & semak paparan senarai tempahan', () => {
    // Layari halaman bookingterkini.php
    cy.visit('/view/bookingterkini.php');

    // Sahkan komponen asas halaman wujud
    cy.contains('Active Bookings').should('be.visible');
    cy.get('#bookings_list').should('exist');
  });

  it('Langkah 2: Uji pembukaan modal laporan (Report Issue Modal)', () => {
    cy.visit('/view/bookingterkini.php');

    // Tunggu senarai tempahan selesai diambil
    cy.get('#bookings_list').should('not.contain', 'Fetching');

    // Jika ada tempahan aktif, cuba tekan butang "Report" (berwarna oren / kuning)
    cy.get('body').then(($body) => {
      if ($body.find('button[onclick*="openReportModal"]').length > 0) {
        // Klik butang Report
        cy.get('button[onclick*="openReportModal"]').first().click();

        // Modal patut terpapar
        cy.get('#reportModal').should('be.visible');

        // Isi laporan ringkas
        cy.get('#reportType').select('Progress Updates Missing');
        cy.get('#reportDesc').type('Sitter tidak menghantar sebarang kemaskini foto harian kucing saya.');

        // Tutup modal semula
        cy.get('#reportModal .close').click();
        cy.get('#reportModal').should('not.be.visible');
      } else {
        cy.log('Tiada tempahan aktif buat masa ini untuk menguji butang Report.');
      }
    });
  });

  it('Langkah 3: Uji butang tindakan pembatalan / penyiapan servis', () => {
    cy.visit('/view/bookingterkini.php');
    cy.get('#bookings_list').should('not.contain', 'Fetching');

    cy.get('body').then(($body) => {
      // Periksa jika butang Request Cancel wujud
      if ($body.find('button[onclick*="requestCancel"]').length > 0) {
        // Tangkap prompt pengesahan
        cy.on('window:confirm', () => true);
        
        // Klik butang Request Cancel
        cy.get('button[onclick*="requestCancel"]').first().click();
        cy.log('Berjaya menekan butang minta batal tempahan.');
      } else {
        cy.log('Tiada tempahan aktif yang boleh dibatalkan.');
      }
    });
  });

});
