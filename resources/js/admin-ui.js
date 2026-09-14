document.addEventListener('alpine:init', () => {
    Alpine.store('ui', {
        isDark: localStorage.getItem('td-theme') === 'dark'
                || (!('td-theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches),

        toggleTheme() {
            this.isDark = !this.isDark;
            localStorage.setItem('td-theme', this.isDark ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', this.isDark);
        },

        sidebarOpen: false,
        toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; },
        closeSidebar()  { this.sidebarOpen = false; },
    });

    document.documentElement.classList.toggle('dark', Alpine.store('ui').isDark);
});
