//
document.addEventListener(
    'livewire:init',
    () => {
        Livewire.on(
            'scroll-to-mission',
            () => {
                document
                    .getElementById('current-mission')
                    ?.scrollIntoView({
                        behavior: 'smooth'
                    });
            }
        );
    }
);