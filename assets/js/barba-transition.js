barba.init({
    transitions: [{
        name: 'route-based-bg-color',
        leave(data) {
            const nextUrl = data.next.url.path;
            let newBg = '#ffffff';
            if (nextUrl.includes('/evolution')) {
                newBg = '#ccfdf3';
            } else if (nextUrl.includes('/average')) {
                newBg = '#f5f5dc';
            }
            gsap.to(document.body, {
                backgroundColor: newBg,
                duration: 0.6
            });
            return gsap.to(data.current.container, {
                opacity: 0,
                duration: 0.5
            });
        },
        enter(data) {
            return gsap.from(data.next.container, {
                opacity: 0,
                duration: 0.5
            });
        }
    }]
});
