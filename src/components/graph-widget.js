// WordPress dependancies.
import { __ } from '@wordpress/i18n';
import { Fragment, useState, useEffect } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

// External dependancies.
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

const GraphWidget = () => {
	const [ days, setDays ] = useState( '7' );
	const [ data, setData ] = useState( [] );
	
	useEffect( () => {
		const day = ( days ) ? days : '7';
		const queryParams = { days: day };
		apiFetch( { path: addQueryArgs( '/rmdash/v1/graphdata', queryParams ) } ).then( ( result) => {
			setData( result );
		} )
	}, [ days ] );

	return (
		<Fragment>
			<div className='header-wrap'>
				<h4>{__( 'Graph Widget', 'rankmath-dashboard-widget' ) }</h4>
				<SelectControl 
					value={ days }
					options={ [
						{ label: __( 'Last 7 Days', 'rankmath-dashboard-widget' ), value: '7' },
						{ label: __( 'Last 15 Days', 'rankmath-dashboard-widget' ), value: '15' },
						{ label: __( 'Last 1 Month', 'rankmath-dashboard-widget' ), value: '30' },
					] }
					onChange={ ( value ) => setDays( value ) }
				/>
			</div>
			<ResponsiveContainer minWidth={260} minHeight={240}>
				<AreaChart
					data={data}
					margin={{
						top: 10,
						right: 30,
						left: 0,
						bottom: 0,
					}}
				>
					<CartesianGrid strokeDasharray="3 3" />
					<XAxis dataKey="name" />
					<YAxis />
					<Tooltip />
					<Area type="monotone" dataKey="uv" stroke="#8884d8" fill="#8884d8" />
				</AreaChart>
			</ResponsiveContainer>
		</Fragment>
	)
}

export default GraphWidget;