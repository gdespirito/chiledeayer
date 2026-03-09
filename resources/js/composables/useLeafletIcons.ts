import L from 'leaflet';

let fixed = false;

export function useLeafletIcons(): void {
    if (fixed) return;
    fixed = true;

    delete (L.Icon.Default.prototype as any)._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: new URL(
            'leaflet/dist/images/marker-icon-2x.png',
            import.meta.url,
        ).href,
        iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url)
            .href,
        shadowUrl: new URL(
            'leaflet/dist/images/marker-shadow.png',
            import.meta.url,
        ).href,
    });
}
