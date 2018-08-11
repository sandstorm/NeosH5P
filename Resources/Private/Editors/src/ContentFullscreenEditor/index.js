import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {neos} from '@neos-project/neos-ui-decorators';
import {Button} from '@neos-project/react-ui-components/';

export default class ContentFullscreenEditor extends PureComponent {
    static propTypes = {
        action: PropTypes.string.isRequired,
        onContentPicked: PropTypes.func.isRequired,
        currentContent: PropTypes.shape({
            contentId: PropTypes.string.isRequired,
            title: PropTypes.string.isRequired
        }),
        doNotAppendToQuery: PropTypes.boolean
    };

    render() {
        window.NeosH5PBrowserCallbacks = {
            contentPicked: this.props.onContentPicked,
            contentEdit: this.props.onContentEdit,
            contentDelete: this.props.onContentDelete,
            currentContent: this.props.currentContent
        };
        return <iframe
            src={'/neosh5p/contentfullscreeneditor/' + this.props.action + (this.props.currentContent && !this.props.doNotAppendToQuery ? '/' + this.props.currentContent.persistenceObjectIdentifier : '')}
            style={{
                position: 'absolute',
                width: '100%',
                height: '100%',
                border: '0'
            }}/>
    }
}
