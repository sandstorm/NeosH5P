import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {neos} from '@neos-project/neos-ui-decorators';
import {Button} from '@neos-project/react-ui-components/';

export default class ContentFullscreenEditor extends PureComponent {
    static propTypes = {
        action: PropTypes.string.isRequired,
        onContentChosen: PropTypes.func.isRequired,
        currentContent: PropTypes.shape({
            persistenceObjectIdentifier: PropTypes.string.isRequired,
            contentId: PropTypes.string.isRequired,
            title: PropTypes.string.isRequired
        }),
        doNotAppendToQuery: PropTypes.boolean
    };

    render() {
        let iframe;
        const setRef = ref => {
            iframe = ref;
        };
        const {onContentChosen, currentContent} = this.props;

        window.NeosH5PBrowserCallbacks = {
            chooseContent: onContentChosen,
            currentContent: currentContent,
            chooseContentAfterSaving() {
                // Wait for iframe to finish saving
                iframe.contentWindow.addEventListener('unload', () => {
                    onContentChosen(...arguments);
                });
            }
        };
        return <iframe
            src={'/neosh5p/contentfullscreeneditor/' + this.props.action + (this.props.currentContent && !this.props.doNotAppendToQuery ? '/' + this.props.currentContent.persistenceObjectIdentifier : '')}
            ref={setRef}
            style={{
                position: 'absolute',
                width: '100%',
                height: '100%',
                border: '0'
            }}/>
    }
}
