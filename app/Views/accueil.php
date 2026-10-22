<!DOCTYPE html>
<html class="light" lang="fr">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Aura Finance - Accueil</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
        <link href="<?= base_url('assets/css/aura-finance.css') ?>" rel="stylesheet" />
    <script src="<?= base_url('assets/js/tailwind-config.js') ?>"></script>
</head>

<body class="bg-background text-on-background font-body-lg overflow-x-hidden min-h-screen">

    <!-- Top Navigation -->
    <header class="fixed top-0 left-0 w-full z-50 flex justify-between items-center px-container-margin py-xs shadow-sm bg-surface">
        <div class="font-headline-lg-mobile text-headline-lg-mobile font-bold text-primary">Aura Finance</div>
        <a class="material-symbols-outlined text-secondary hover:bg-surface-container-high transition-colors p-base rounded-full" href="<?= base_url('logout') ?>">logout</a>
    </header>

    <main class="pt-20 pb-28 px-container-margin max-w-[1200px] mx-auto w-full">
        <?php if (session()->has('error')): ?>
            <div class="mb-lg p-md bg-red-100 border border-red-400 text-red-700 rounded-lg text-center font-bold">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->has('success')): ?>
            <div class="mb-lg p-md bg-green-100 border border-green-400 text-green-700 rounded-lg text-center font-bold">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <!-- Balance Card -->
        <div class="bg-inverse-surface rounded-xl p-lg shadow-md flex flex-col justify-between min-h-[220px] mb-md">
            <span class="text-surface-variant/80 font-label-caps uppercase tracking-widest">Solde Actuel</span>
            <div class="flex items-baseline gap-xs mt-base">
                <span class="text-primary-container font-display-lg text-display-lg"><?= number_format($client['solde'], 0, ',', '.') ?></span>
                <span class="text-primary-container font-headline-lg text-headline-lg">Ar</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="grid grid-cols-3 gap-sm mb-xl">
            <button class="flex flex-col items-center p-md bg-primary-container text-on-primary-container rounded-xl shadow-sm active:scale-95 transition-all" onclick="openModal('dépôt')">
                <span class="material-symbols-outlined">add_circle</span>
                <span class="text-label-caps">Dépôt</span>
            </button>
            <button class="flex flex-col items-center p-md bg-secondary text-on-secondary rounded-xl shadow-sm active:scale-95 transition-all" onclick="openModal('retrait')">
                <span class="material-symbols-outlined">outbox</span>
                <span class="text-label-caps">Retrait</span>
            </button>
            <button class="flex flex-col items-center p-md bg-surface-container-high text-on-surface rounded-xl shadow-sm active:scale-95 transition-all" onclick="openModal('transfert')">
                <span class="material-symbols-outlined">send</span>
                <span class="text-label-caps">Transfert</span>
            </button>
        </div>

        <!-- Activités Récentes -->
        <div class="mt-lg">
            <div class="flex justify-between items-center mb-md">
                <h3 class="font-title-md text-title-md">Activités Récentes</h3>
                <a href="<?= base_url('client/historique') ?>" class="text-primary font-medium text-sm hover:underline">Voir tout</a>
            </div>
            <div class="space-y-sm">
                <?php foreach ($activites as $op): ?>
                    <div class="flex items-center justify-between p-sm bg-white rounded-lg shadow-sm border border-outline-variant/10">
                        <div class="flex items-center gap-md">
                            <div class="w-10 h-10 bg-surface-container rounded-full flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary">
                                    <?= $op['libelle'] == 'dépôt' ? 'add_circle' : ($op['libelle'] == 'retrait' ? 'outbox' : 'send') ?>
                                </span>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface capitalize"><?= esc($op['libelle']) ?></p>
                                <p class="text-xs text-outline"><?= $op['date_operation'] ?></p>
                            </div>
                        </div>
                        <p class="text-primary font-bold">
                            <?= ($op['libelle'] == 'dépôt') ? '+' : '-' ?>
                            <?= number_format($op['montant'], 0, ',', '.') ?> Ar
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <!-- Modal -->
    <div class="fixed inset-0 z-[60] bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center transition-opacity opacity-0 pointer-events-none p-container-margin" id="action-modal">
        <div class="bg-surface w-full max-w-md rounded-2xl p-lg transform translate-y-full transition-transform" id="modal-content">
            <form action="" method="post" id="action-form">
                <?= csrf_field() ?>
                <div class="flex justify-between items-center mb-lg">
                    <h3 class="font-title-md text-title-md capitalize" id="modal-title">Action</h3>
                    <button type="button" class="material-symbols-outlined" onclick="closeModal()">close</button>
                </div>
                <div class="space-y-lg">
                    <div>
                        <label class="block text-xs font-bold text-outline uppercase mb-xs">Montant (Ar)</label>
                        <input name="montant" class="w-full text-display-lg border-b-2 border-outline-variant focus:border-primary bg-transparent p-0" type="number" required />
                    </div>
                    <div id="destinataire-group" class="hidden">
                        <label class="block text-xs font-bold text-outline uppercase mb-xs">Numéros (ex: 033..., 034...)</label>
                        <textarea name="destinataire" id="destinataire-input" class="w-full text-headline-sm border-b-2 border-outline-variant focus:border-primary bg-transparent p-0" rows="2"></textarea>
                    </div>
                    <div id="frais-transfert-group" class="hidden p-sm rounded-lg bg-orange-50 border-l-4 border-orange-500">
                        <div class="flex items-center gap-sm">
                            <input type="checkbox" name="inclure_frais_retrait" id="inclure_frais_input" value="on">
                            <label for="inclure_frais_input" class="text-sm">Inclure frais de retrait (destinataire)</label>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-md bg-primary text-on-primary rounded-lg font-bold">Confirmer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const prefixesAura = <?= json_encode(session()->get('prefixes') ?? []) ?>;
        const modal = document.getElementById('action-modal');
        const modalContent = document.getElementById('modal-content');
        const destInput = document.getElementById('destinataire-input');
        const fraisGroup = document.getElementById('frais-transfert-group');
        const checkbox = document.getElementById('inclure_frais_input');

        destInput.addEventListener('input', function() {
            const val = this.value.replace(/\s/g, '');
            const numeros = val.split(',').filter(n => n.length > 0);
            const tousInternes = numeros.length > 0 && numeros.every(num => prefixesAura.some(pref => num.startsWith(pref)));

            if (tousInternes) {
                fraisGroup.classList.remove('hidden');
                checkbox.disabled = false;
            } else {
                fraisGroup.classList.add('hidden');
                checkbox.checked = false;
                checkbox.disabled = true;
            }
        });

        window.openModal = function(type) {
            document.getElementById('modal-title').innerText = type;
            
            const actions = {
                'dépôt': "<?= base_url('client/depot') ?>",
                'retrait': "<?= base_url('client/retrait') ?>",
                'transfert': "<?= base_url('client/transfert') ?>"
            };
            document.getElementById('action-form').action = actions[type] || '';
            
            const destGroup = document.getElementById('destinataire-group');
            if (type === 'transfert') {
                destGroup.classList.remove('hidden');
                destInput.setAttribute('required', 'required');
            } else {
                destGroup.classList.add('hidden');
                destInput.removeAttribute('required');
                fraisGroup.classList.add('hidden');
                checkbox.checked = false;
                checkbox.disabled = true;
            }
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            setTimeout(() => modalContent.classList.remove('translate-y-full'), 10);
        };

        window.closeModal = function() {
            modalContent.classList.add('translate-y-full');
            setTimeout(() => modal.classList.add('opacity-0', 'pointer-events-none'), 300);
        };
    </script>
</body>
</html>