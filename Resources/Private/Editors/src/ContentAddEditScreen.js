import React, {PureComponent} from 'react';
import PropTypes from 'prop-types';
import {neos} from '@neos-project/neos-ui-decorators';
import {Button} from '@neos-project/react-ui-components/';


export default class ContentAddEditScreen extends PureComponent {
    static propTypes = {
        contentPersistenceObjectId: PropTypes.string.isRequired
    };

    render() {
        // TODO: Remove all inline styles and put them into a css file
        if (this.props.contentPersistenceObjectId !== '') {
            return <iframe src="http://127.0.0.1:8081/neosh5p/content/add" style={{width: "100%", height: "100%"}}/>
        } else {
            return <iframe src={"http://127.0.0.1:8081/neosh5p/content/edit/contentPersistenceObjectId"} style={{width: "100%", height: "100%"}}/>
        }
    }
}
