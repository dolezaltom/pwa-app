const View = new class {
    get template() {
        return document.querySelector('#image-card');
    }
    get grid() {
        return document.querySelector('#images-list');
    }

    renderImage(image) {
        let card = document.importNode(this.template.content, true);
        card.querySelector('.imageSrc').setAttribute('src', image.urls.small);
        card.querySelector('.image-author').innerText = image.user.name;
        card.querySelector('.image-download').setAttribute('href', image.links.download);
        card.querySelector('.image-download').addEventListener('click', this.onDownload);
        card.querySelector('.image-download').setAttribute('data-backup-download', image.urls.small);
        card.querySelector('.image-open').setAttribute('href', image.links.html);
        this.grid.appendChild(card);
    }

    onDownload(event) {
        if (!navigator.onLine) {
            event.preventDefault();
            alert('Downloading is disabled while offline');
        }
    }

    renderImages(images) {
        images.forEach(image => this.renderImage(image));
    }
}

const UnsplashApi = new class {
    get apiHost() {
        return 'https://api.unsplash.com';
    }
    get clientId() {
        return '9df26b67b558143fa29670ab7d4f02e8a87dec739e20cc95d1dfac3553dc9bf2';
    }

    random() {
        return this.get('photos/random', { count: 30, collections: 1353633 });
    }

    photos() {
        return this.get('photos', { per_page: 30 });
    }

    get(path, params) {
        let init = {
            method: 'GET',
            headers: this.headers()
        }
        return fetch(this.constructUrl(path, params), init)
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    throw response;
                }
            });
    }

    constructUrl(path, params) {
        return `${this.apiHost}/${path}${this.query(params)}`;
    }

    query(params) {
        if (params) {
            return '?' + Object.keys(params).map(k => `${encodeURIComponent(k)}=${encodeURIComponent(params[k])}`).join('&');
        } else {
            return '';
        }
    }

    headers() {
        return new Headers({
            'Authorization': `Client-ID ${this.clientId}`
        });
    }
}

const App = new class {
    get view() {
        return View;
    }
    get api() {
        return UnsplashApi;
    }

    init() {
        this.api.random().then(images => this.view.renderImages(images))
            .catch(error => {
                console.log('[APP] Error getting photos', error)
            });
    }
}

App.init();