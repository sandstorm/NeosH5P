import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {neos} from '@neos-project/neos-ui-decorators';
import {Button} from '@neos-project/react-ui-components/';


export default class ContentDisplayScreen extends PureComponent {
    static propTypes = {
        contentPersistenceObjectId: PropTypes.string.isRequired,
        onContentPicked: PropTypes.func.isRequired,
        onContentEdit: PropTypes.func.isRequired,
        onContentDelete: PropTypes.func.isRequired,
    };

    render() {
        window.NeosH5PBrowserCallbacks = {
            contentPicked: this.props.onContentPicked,
            contentEdit: this.props.onContentEdit,
            contentDelete: this.props.onContentDelete
        };

        // TODO: Remove all inline styles and put them into a css file
        return <iframe src={"http://127.0.0.1:8081/neosh5p/content/display/" + this.props.contentPersistenceObjectId} style={{width: "100%", height: "100%"}}/>
    }
}
