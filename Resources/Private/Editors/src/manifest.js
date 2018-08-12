import manifest from '@neos-project/neos-ui-extensibility'
import ContentFullscreenEditor from './ContentFullscreenEditor/index'
import ContentPickerEditor from './ContentPickerEditor/index'

manifest('Sandstorm.NeosH5P:ContentPickerEditor', {}, globalRegistry => {
    const editorsRegistry = globalRegistry.get('inspector').get('editors');
    const secondaryEditorsRegistry = globalRegistry.get('inspector').get('secondaryEditors');

    editorsRegistry.set('Sandstorm.NeosH5P/ContentPickerEditor', {
        component: ContentPickerEditor
    });

    secondaryEditorsRegistry.set('Sandstorm.NeosH5P/ContentFullscreenEditor', {
        component: ContentFullscreenEditor
    });
});
