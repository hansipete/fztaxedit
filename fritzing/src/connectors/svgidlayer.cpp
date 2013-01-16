/*******************************************************************

Part of the Fritzing project - http://fritzing.org
Copyright (c) 2007-2011 Fachhochschule Potsdam - http://fh-potsdam.de

Fritzing is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fritzing is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fritzing.  If not, see <http://www.gnu.org/licenses/>.

********************************************************************

$Revision: 6650 $:
$Author: irascibl@gmail.com $:
$Date: 2012-11-22 23:19:08 +0100 (Thu, 22 Nov 2012) $

********************************************************************/

#include "svgidlayer.h"

SvgIdLayer::SvgIdLayer(ViewLayer::ViewIdentifier viewIdentifier) {
    m_viewIdentifier = viewIdentifier;
	m_path = m_processed = m_hybrid = false;
	m_radius = m_strokeWidth = 0;
}

SvgIdLayer * SvgIdLayer::copyLayer() {
	SvgIdLayer * toSvgIdLayer = new SvgIdLayer(m_viewIdentifier);
	*toSvgIdLayer = *this;
	return toSvgIdLayer;
}



