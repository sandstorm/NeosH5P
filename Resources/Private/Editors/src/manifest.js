import manifest from '@neos-project/neos-ui-extensibility'
import ContentAddEditScreen from './ContentAddEditScreen'
import ContentDisplayScreen from './ContentDisplayScreen'
import ContentListScreen from './ContentListScreen'
import ContentPickerEditor from './ContentPickerEditor'

manifest('Sandstorm.NeosH5P:ContentPickerEditor', {}, globalRegistry => {
    const editorsRegistry = globalRegistry.get('inspector').get('editors');
    const secondaryEditorsRegistry = globalRegistry.get('inspector').get('secondaryEditors');

    editorsRegistry.set('Sandstorm.NeosH5P/ContentPickerEditor', {
        component: ContentPickerEditor
    });

    secondaryEditorsRegistry.set('Sandstorm.NeosH5P/ContentAddEditScreen', {
        component: ContentAddEditScreen
    });

    secondaryEditorsRegistry.set('Sandstorm.NeosH5P/ContentDisplayScreen', {
        component: ContentDisplayScreen
    });

    secondaryEditorsRegistry.set('Sandstorm.NeosH5P/ContentListScreen', {
        component: ContentListScreen
    });
});
