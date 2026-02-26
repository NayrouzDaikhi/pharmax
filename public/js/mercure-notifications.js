document.addEventListener('DOMContentLoaded', function() {
    // Configuration Mercure pour les notifications d'expiration de produits
    const mercureUrl = new URL('{{ mercure_publish_url }}', window.location.origin);
    const topic = 'https://{{ app.request.host }}/product/expiring';

    // Vérifier si Mercure est configuré
    if (!mercureUrl.href || mercureUrl.href === 'null') {
        console.warn('MercureBundle non configuré. Les notifications temps réel ne fonctionneront pas.');
        return;
    }

    // Créer une connexion EventSource pour recevoir les notifications
    const eventSource = new EventSource(
        mercureUrl.href + '?topic=' + encodeURIComponent(topic),
        { withCredentials: true }
    );

    // Gérer les messages reçus
    eventSource.onmessage = function(event) {
        try {
            const data = JSON.parse(event.data);
            
            if (data.type === 'product_expiring') {
                // Créer une notification dans le navigateur
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('Produit en Expiration', {
                        body: data.message || data.productName + ' expire le ' + data.expirationDate,
                        icon: '/images/warning.png',
                        tag: 'product-' + data.productId
                    });
                }
                
                // Mettre à jour la liste des notifications en page
                updateExpiringProductsList(data);
            }
        } catch (e) {
            console.error('Erreur lors du traitement de la notification:', e);
        }
    };

    // Gérer les erreurs
    eventSource.onerror = function() {
        console.error('Erreur de connexion à Mercure');
        eventSource.close();
    };

    // Fonction pour mettre à jour l'affichage des produits en expiration
    function updateExpiringProductsList(data) {
        const listGroup = document.querySelector('.list-group');
        if (!listGroup) return;

        // Chercher si le produit existe déjà dans la liste
        let productElement = listGroup.querySelector('[data-product-id="' + data.productId + '"]');
        
        if (!productElement) {
            // Créer un nouvel élément pour le produit
            const newItem = document.createElement('div');
            newItem.className = 'list-group-item list-group-item-warning';
            newItem.setAttribute('data-product-id', data.productId);
            newItem.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="badge bg-danger">${data.productName}</span>
                        <small class="text-muted ms-2">
                            Expire le: <strong>${data.expirationDate}</strong>
                        </small>
                    </div>
                    <a href="/admin/produit/${data.productId}" class="btn btn-primary btn-sm">
                        <i class="bx bx-show"></i> Voir
                    </a>
                </div>
            `;
            listGroup.insertBefore(newItem, listGroup.firstChild);
        }
    }

    // Demander la permission pour les notifications navigateur
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
});